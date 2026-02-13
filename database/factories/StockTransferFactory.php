<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 *
 * Source and destination are separate factory() calls,
 * guaranteeing different warehouses automatically.
 */
class StockTransferFactory extends Factory
{
    protected $model = StockTransfer::class;

    public function definition(): array
    {
        return [
            'source_warehouse_id'      => Warehouse::factory(),
            'destination_warehouse_id' => Warehouse::factory(),
            'inventory_item_id'        => InventoryItem::factory(),
            'quantity'                 => $this->faker->numberBetween(1, 100),
            'status'                   => TransferStatus::Completed,
            'reference_number'         => $this->generateReferenceNumber(),
            'notes'                    => $this->faker->optional()->sentence(),
            'transferred_by'           => User::factory(),
        ];
    }

    protected function generateReferenceNumber(): string
    {
        return $this->faker->unique()->bothify(
            'TRF-' . now()->format('Ymd') . '-????##'
        );
    }

    public function status(TransferStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }
}