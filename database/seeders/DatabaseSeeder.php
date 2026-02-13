<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function () {

            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name'     => 'Test User',
                    'password' => bcrypt('password'),
                ]
            );

            $warehouses = Warehouse::factory(5)->create();
            Warehouse::factory()->inactive()->create([
                'name' => 'Closed Warehouse',
            ]);

            $items = InventoryItem::factory(30)->create();

            $warehouses->each(function ($warehouse) use ($items) {
                $items->random(rand(10, 20))->each(function ($item) use ($warehouse) {
                    Stock::firstOrCreate(
                        [
                            'warehouse_id'      => $warehouse->id,
                            'inventory_item_id' => $item->id,
                        ],
                        [
                            'quantity'            => rand(10, 500),
                            'low_stock_threshold' => 10,
                        ]
                    );
                });
            });

            $firstWarehouse = $warehouses->first();
            $existingItemIds = Stock::where('warehouse_id', $firstWarehouse->id)
                ->pluck('inventory_item_id')
                ->all();

            $items->whereNotIn('id', $existingItemIds)->take(3)->each(function ($item) use ($firstWarehouse) {
                Stock::factory()->lowStock()->create([
                    'warehouse_id'      => $firstWarehouse->id,
                    'inventory_item_id' => $item->id,
                ]);
            });
        });
    }
}
