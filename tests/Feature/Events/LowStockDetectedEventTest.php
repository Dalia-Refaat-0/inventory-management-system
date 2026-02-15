<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\LowStockDetected;
use App\Listeners\NotifyWarehouseManager;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use DateTimeImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(LowStockDetected::class)]
#[CoversClass(NotifyWarehouseManager::class)]
final class LowStockDetectedEventTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    private function createTransferScenario(
        int $sourceQuantity,
        int $threshold,
    ): array {
        $source = Warehouse::factory()->create();
        $destination = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $stock = Stock::factory()->create([
            'warehouse_id' => $source->id,
            'inventory_item_id' => $item->id,
            'quantity' => $sourceQuantity,
            'low_stock_threshold' => $threshold,
        ]);

        return compact('source', 'destination', 'item', 'stock');
    }

    private function performTransfer(array $scenario, int $quantity): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/stock-transfers', [
            'source_warehouse_id' => $scenario['source']->id,
            'destination_warehouse_id' => $scenario['destination']->id,
            'inventory_item_id' => $scenario['item']->id,
            'quantity' => $quantity,
        ]);
    }


    #[Test]
    public function it_dispatches_event_when_stock_falls_below_threshold(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $scenario = $this->createTransferScenario(sourceQuantity: 12, threshold: 10);

        $this->performTransfer($scenario, quantity: 5)->assertCreated();

        Event::assertDispatched(LowStockDetected::class);
    }

    #[Test]
    public function it_dispatches_event_with_correct_properties(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        $scenario = $this->createTransferScenario(sourceQuantity: 12, threshold: 10);

        $this->performTransfer($scenario, quantity: 5)->assertCreated();

        Event::assertDispatched(
            LowStockDetected::class,
            function (LowStockDetected $event) use ($scenario) {
                $this->assertInstanceOf(Stock::class, $event->stock);
                $this->assertEquals($scenario['source']->id, $event->stock->warehouse_id);
                $this->assertEquals($scenario['item']->id, $event->stock->inventory_item_id);
                $this->assertEquals(7, $event->currentQuantity);
                $this->assertEquals(10, $event->threshold);
                $this->assertInstanceOf(DateTimeImmutable::class, $event->detectedAt);

                return true;
            },
        );
    }

    #[Test]
    public function it_does_not_dispatch_event_when_stock_remains_above_threshold(): void
    {
        Event::fake([LowStockDetected::class]);
        $this->authenticate();

        // quantity: 100, threshold: 10, transfer: 5 → remaining: 95
        $scenario = $this->createTransferScenario(sourceQuantity: 100, threshold: 10);

        $this->performTransfer($scenario, quantity: 5)->assertCreated();

        Event::assertNotDispatched(LowStockDetected::class);
    }


    #[Test]
    public function it_supports_static_dispatch_helper_with_correct_data(): void
    {
        Event::fake([LowStockDetected::class]);

        $stock = Stock::factory()->create([
            'quantity' => 3,
            'low_stock_threshold' => 10,
        ]);

        LowStockDetected::dispatchFor($stock);

        Event::assertDispatched(
            LowStockDetected::class,
            fn (LowStockDetected $e) => $e->stock->id === $stock->id
                && $e->currentQuantity === 3
                && $e->threshold === 10,
        );
    }


    #[Test]
    public function it_binds_listener_to_event(): void
    {
        Event::fake();

        Event::assertListening(
            LowStockDetected::class,
            NotifyWarehouseManager::class,
        );
    }

    #[Test]
    public function it_configures_listener_as_queued_with_retry_policy(): void
    {
        $this->assertTrue(
            is_subclass_of(NotifyWarehouseManager::class, ShouldQueue::class)
            || in_array(ShouldQueue::class, class_implements(NotifyWarehouseManager::class) ?: []),
            'NotifyWarehouseManager must implement ShouldQueue',
        );

        $listener = new NotifyWarehouseManager();

        $this->assertSame(3, $listener->tries, 'Listener should retry up to 3 times');
        $this->assertSame(60, $listener->backoff, 'Listener should wait 60s between retries');
    }
}