<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 *
 * No DB queries. Pure data generation.
 * Caller provides specific IDs when needed.
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'warehouse_id'        => Warehouse::factory(),
            'inventory_item_id'   => InventoryItem::factory(),
            'quantity'            => $this->faker->numberBetween(10, 500),
            'low_stock_threshold' => 10,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'quantity'            => $this->faker->numberBetween(0, 5),
            'low_stock_threshold' => 10,
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn () => [
            'quantity' => 0,
        ]);
    }

    public function abundant(): static
    {
        return $this->state(fn () => [
            'quantity' => $this->faker->numberBetween(500, 5000),
        ]);
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn () => [
            'quantity' => $quantity,
        ]);
    }

    public function withThreshold(int $threshold): static
    {
        return $this->state(fn () => [
            'low_stock_threshold' => $threshold,
        ]);
    }
}