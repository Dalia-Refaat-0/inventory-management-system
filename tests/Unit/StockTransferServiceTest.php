<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\StockRepositoryInterface;
use App\Contracts\StockTransferRepositoryInterface;
use App\DTOs\TransferStockData;
use App\Exceptions\InsufficientStockException;
use App\Models\Stock;
use App\Models\User;
use App\Services\StockTransferService;
use App\Support\TransferReferenceGenerator;
use Illuminate\Testing\TestResponse;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @covers \App\Services\StockTransferService
 */
final class StockTransferServiceTest extends TestCase
{
    private StockTransferService $service;
    private StockRepositoryInterface|MockInterface $stockRepo;
    private StockTransferRepositoryInterface|MockInterface $transferRepo;
    private TransferReferenceGenerator|MockInterface $referenceGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockRepo = Mockery::mock(StockRepositoryInterface::class);
        $this->transferRepo = Mockery::mock(StockTransferRepositoryInterface::class);
        $this->referenceGenerator = Mockery::mock(TransferReferenceGenerator::class);

        $this->service = new StockTransferService(
            $this->stockRepo,
            $this->transferRepo,
            $this->referenceGenerator,
        );
    }

    #[Test]
    public function it_prevents_stock_transfer_when_requested_quantity_exceeds_available_inventory(): void
    {
        $available = 10;
        $requested = 50;
        $user = User::factory()->make(['id' => 1]);

        $sourceStock = $this->createMockStock(quantity: $available);
        $transferData = $this->createTransferData(quantity: $requested);

        $this->stockRepo
            ->shouldReceive('findForWarehouseItem')
            ->once()
            ->with($transferData->source_warehouse_id, $transferData->inventory_item_id, true)
            ->andReturn($sourceStock);

        $this->stockRepo->shouldNotReceive('decrementQuantity', 'incrementQuantity', 'deleteIfEmpty');
        $this->transferRepo->shouldNotReceive('create');

        try {
            $this->service->transfer($transferData, $user);
            $this->fail('InsufficientStockException was not thrown despite stock deficit.');
        } catch (InsufficientStockException $e) {
            $this->assertInsufficientStockResponse($e, $requested, $available);
        }
    }

    /**
     * Refactored assertion logic to keep the main test clean.
     */
    private function assertInsufficientStockResponse(InsufficientStockException $e, int $requested, int $available): void
    {
        $this->assertEquals('Insufficient stock for transfer.', $e->getMessage());

        $response = new TestResponse($e->render());

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['quantity']])
            ->assertJsonPath('errors.quantity.0', function (string $message) use ($requested, $available) {
                return str_contains($message, (string) $requested) && 
                    str_contains($message, (string) $available) &&
                    str_contains($message, 'You requested');
            });
    }


    private function createMockStock(int $quantity): Stock
    {
        $stock = new Stock([
            'id' => 1,
            'warehouse_id' => 1,
            'inventory_item_id' => 100,
            'quantity' => $quantity,
        ]);
        $stock->exists = true;
        return $stock;
    }

    private function createTransferData(int $quantity): TransferStockData
    {
        return TransferStockData::from([
            'source_warehouse_id' => 1,
            'destination_warehouse_id' => 2,
            'inventory_item_id' => 100,
            'quantity' => $quantity,
            'notes' => 'Unit testing deficit',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}