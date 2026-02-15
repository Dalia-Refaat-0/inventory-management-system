<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransferStatus;
use App\Events\LowStockDetected;
use App\Http\Controllers\StockTransferController;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * End-to-end integration tests for POST /api/stock-transfers.
 */
#[CoversClass(StockTransferController::class)]
#[CoversClass(StockTransferService::class)]
final class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/stock-transfers';

    private function authenticate(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * @return array{
     *     source: Warehouse,
     *     destination: Warehouse,
     *     item: InventoryItem,
     *     sourceStock: Stock,
     *     destinationStock: Stock|null,
     * }
     */
    private function createScenario(
        int $sourceQuantity = 100,
        int $destinationQuantity = 20,
        int $threshold = 10,
        bool $createDestinationStock = true,
    ): array {
        $source = Warehouse::factory()->create();
        $destination = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $sourceStock = Stock::factory()->create([
            'warehouse_id' => $source->id,
            'inventory_item_id' => $item->id,
            'quantity' => $sourceQuantity,
            'low_stock_threshold' => $threshold,
        ]);

        $destinationStock = null;
        if ($createDestinationStock) {
            $destinationStock = Stock::factory()->create([
                'warehouse_id' => $destination->id,
                'inventory_item_id' => $item->id,
                'quantity' => $destinationQuantity,
                'low_stock_threshold' => $threshold,
            ]);
        }

        return compact('source', 'destination', 'item', 'sourceStock', 'destinationStock');
    }

    private function payload(array $scenario, int $quantity, ?string $notes = null): array
    {
        $data = [
            'source_warehouse_id' => $scenario['source']->id,
            'destination_warehouse_id' => $scenario['destination']->id,
            'inventory_item_id' => $scenario['item']->id,
            'quantity' => $quantity,
        ];

        if ($notes !== null) {
            $data['notes'] = $notes;
        }

        return $data;
    }

    private function transfer(array $scenario, int $quantity, ?string $notes = null): TestResponse
    {
        return $this->postJson(self::ENDPOINT, $this->payload($scenario, $quantity, $notes));
    }

    #[Test]
    public function it_returns_401_when_unauthenticated(): void
    {
        $this->postJson(self::ENDPOINT, [])->assertUnauthorized();
    }

    #[Test]
    #[DataProvider('requiredFieldsProvider')]
    public function it_validates_required_fields(string $missingField): void
    {
        $this->authenticate();
        $scenario = $this->createScenario();

        $payload = $this->payload($scenario, quantity: 5);
        unset($payload[$missingField]);

        $this->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor($missingField);
    }

    public static function requiredFieldsProvider(): iterable
    {
        yield 'source_warehouse_id' => ['source_warehouse_id'];
        yield 'destination_warehouse_id' => ['destination_warehouse_id'];
        yield 'inventory_item_id' => ['inventory_item_id'];
        yield 'quantity' => ['quantity'];
    }


    #[Test]
    #[DataProvider('invalidQuantityProvider')]
    public function it_rejects_invalid_quantity_values(mixed $invalidQuantity): void
    {
        $this->authenticate();
        $scenario = $this->createScenario();

        $payload = $this->payload($scenario, quantity: 1);
        $payload['quantity'] = $invalidQuantity;

        $this->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('quantity');
    }

    public static function invalidQuantityProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-5];
        yield 'decimal' => [3.5];
        yield 'string' => ['abc'];
    }

    #[Test]
    public function it_rejects_transfer_to_same_warehouse(): void
    {
        $this->authenticate();

        $warehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 50,
        ]);

        $this->postJson(self::ENDPOINT, [
            'source_warehouse_id' => $warehouse->id,
            'destination_warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('destination_warehouse_id');
    }

    #[Test]
    public function it_rejects_non_existent_source_warehouse(): void
    {
        $this->authenticate();
        $destination = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $this->postJson(self::ENDPOINT, [
            'source_warehouse_id' => 99999,
            'destination_warehouse_id' => $destination->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('source_warehouse_id');
    }

    #[Test]
    public function it_rejects_non_existent_inventory_item(): void
    {
        $this->authenticate();
        $scenario = $this->createScenario();

        $payload = $this->payload($scenario, quantity: 5);
        $payload['inventory_item_id'] = 99999;

        $this->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('inventory_item_id');
    }

    #[Test]
    public function it_returns_422_when_transfer_exceeds_available_stock(): void
    {
        $this->authenticate();
        $scenario = $this->createScenario(sourceQuantity: 10);

        $this->transfer($scenario, quantity: 50)
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('quantity');
    }

    #[Test]
    public function it_does_not_mutate_stock_on_insufficient_quantity(): void
    {
        $this->authenticate();
        $scenario = $this->createScenario(sourceQuantity: 10, destinationQuantity: 20);

        $this->transfer($scenario, quantity: 50)->assertUnprocessable();

        $this->assertDatabaseHas('stocks', [
            'id' => $scenario['sourceStock']->id,
            'quantity' => 10,
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $scenario['destinationStock']->id,
            'quantity' => 20,
        ]);
        $this->assertDatabaseCount('stock_transfers', 0);
    }

    #[Test]
    public function it_returns_201_with_correct_json_structure(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario();

        $this->transfer($scenario, quantity: 10, notes: 'Rebalancing')
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference_number',
                    'status',
                    'quantity',
                    'dates' => ['created_at', 'updated_at'],
                ],
            ])
            ->assertJsonPath('data.quantity', 10)
            ->assertJsonPath('data.status', TransferStatus::Completed->value)
            ->assertJsonPath('data.notes', 'Rebalancing');
    }

    #[Test]
    public function it_generates_reference_number_in_correct_format(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario();

        $response = $this->transfer($scenario, quantity: 5)->assertCreated();

        $reference = $response->json('data.reference_number');

        $this->assertNotNull($reference);
        $this->assertMatchesRegularExpression(
            '/^TRF-\d{8}-[A-Z0-9]{8}$/',
            $reference,
            'Expected format: TRF-YYYYMMDD-XXXXXXXX',
        );
    }

    #[Test]
    public function it_accepts_transfer_without_notes(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario();

        $this->transfer($scenario, quantity: 5)->assertCreated();
    }


    #[Test]
    public function it_decrements_source_warehouse_stock(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario(sourceQuantity: 100);

        $this->transfer($scenario, quantity: 30)->assertCreated();

        $this->assertDatabaseHas('stocks', [
            'id' => $scenario['sourceStock']->id,
            'quantity' => 70,
        ]);
    }

    #[Test]
    public function it_increments_destination_warehouse_stock(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario(destinationQuantity: 20);

        $this->transfer($scenario, quantity: 30)->assertCreated();

        $this->assertDatabaseHas('stocks', [
            'id' => $scenario['destinationStock']->id,
            'quantity' => 50,
        ]);
    }

    #[Test]
    public function it_creates_destination_stock_when_none_exists(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario(createDestinationStock: false);

        $this->transfer($scenario, quantity: 15)->assertCreated();

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $scenario['destination']->id,
            'inventory_item_id' => $scenario['item']->id,
            'quantity' => 15,
        ]);
    }

    #[Test]
    public function it_preserves_total_inventory_across_warehouses(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $initialSource = 100;
        $initialDestination = 20;
        $scenario = $this->createScenario(
            sourceQuantity: $initialSource,
            destinationQuantity: $initialDestination,
        );

        $this->transfer($scenario, quantity: 30)->assertCreated();

        $scenario['sourceStock']->refresh();
        $scenario['destinationStock']->refresh();

        $totalBefore = $initialSource + $initialDestination;
        $totalAfter = $scenario['sourceStock']->quantity + $scenario['destinationStock']->quantity;

        $this->assertEquals(
            $totalBefore,
            $totalAfter,
            'Total inventory must remain constant across warehouses',
        );
    }

    #[Test]
    public function it_allows_transferring_entire_available_stock(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();
        $scenario = $this->createScenario(sourceQuantity: 50);

        $this->transfer($scenario, quantity: 50)->assertCreated();

        $this->assertDatabaseMissing('stocks', [
            'id' => $scenario['sourceStock']->id,
        ]);
    }


    #[Test]
    public function it_persists_transfer_record_with_correct_attributes(): void
    {
        Event::fake([LowStockDetected::class]);
        $user = $this->authenticate();
        $scenario = $this->createScenario();

        $this->transfer($scenario, quantity: 25, notes: 'Monthly rebalance')
            ->assertCreated();

        $this->assertDatabaseCount('stock_transfers', 1);

        $transfer = StockTransfer::sole();

        $this->assertEquals($scenario['source']->id, $transfer->source_warehouse_id);
        $this->assertEquals($scenario['destination']->id, $transfer->destination_warehouse_id);
        $this->assertEquals($scenario['item']->id, $transfer->inventory_item_id);
        $this->assertEquals(25, $transfer->quantity);
        $this->assertEquals(TransferStatus::Completed, $transfer->status);
        $this->assertEquals($user->id, $transfer->transferred_by);
        $this->assertEquals('Monthly rebalance', $transfer->notes);
        $this->assertNotNull($transfer->reference_number);
    }

    #[Test]
    public function it_generates_unique_reference_numbers_across_transfers(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $source = Warehouse::factory()->create();
        $destination = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $source->id,
            'inventory_item_id' => $item->id,
            'quantity' => 100,
            'low_stock_threshold' => 5,
        ]);

        $payload = [
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'inventory_item_id' => $item->id,
            'quantity' => 1,
        ];

        $this->postJson(self::ENDPOINT, $payload)->assertCreated();
        $this->postJson(self::ENDPOINT, $payload)->assertCreated();

        $references = StockTransfer::pluck('reference_number')->toArray();

        $this->assertCount(2, $references);
        $this->assertCount(2, array_unique($references), 'Reference numbers must be unique');
    }


    #[Test]
    public function it_dispatches_low_stock_event_when_transfer_breaches_threshold(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $scenario = $this->createScenario(sourceQuantity: 15, threshold: 10);

        $this->transfer($scenario, quantity: 10)->assertCreated();

        Event::assertDispatched(LowStockDetected::class, function (LowStockDetected $event) use ($scenario) {
            return $event->stock->warehouse_id === $scenario['source']->id
                && $event->stock->inventory_item_id === $scenario['item']->id
                && $event->currentQuantity === 5
                && $event->threshold === 10
                && $event->detectedAt instanceof \DateTimeImmutable;
        });
    }

    #[Test]
    public function it_does_not_dispatch_event_when_stock_stays_above_threshold(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $scenario = $this->createScenario(sourceQuantity: 100, threshold: 10);

        $this->transfer($scenario, quantity: 5)->assertCreated();

        Event::assertNotDispatched(LowStockDetected::class);
    }
}