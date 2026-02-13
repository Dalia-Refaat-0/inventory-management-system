<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryItem;
use App\ValueObjects\SKU;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    /**
     * Track used SKUs within a single factory run
     * to guarantee uniqueness without DB queries.
     */
    private static array $usedSkus = [];

    public function definition(): array
    {
        return [
            'name'  => $this->faker->unique()->words(3, true),
            'sku'   => $this->generateUniqueSku(),
            'price' => $this->faker->randomFloat(2, 1.00, 9999.99),
        ];
    }

    /**
     * Generate a unique SKU that won't collide
     * even when creating hundreds of items.
     */
    private function generateUniqueSku(): string
    {
        do {
            $sku = SKU::generate()->value();
        } while (in_array($sku, self::$usedSkus, true));

        self::$usedSkus[] = $sku;

        return $sku;
    }

    public function expensive(): static
    {
        return $this->state(fn () => [
            'price' => $this->faker->randomFloat(2, 1000.00, 9999.99),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn () => [
            'price' => $this->faker->randomFloat(2, 1.00, 49.99),
        ]);
    }
}