<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Rules\ActiveWarehouseExists;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

final class TransferStockData extends Data
{
    public function __construct(
        #[Required, IntegerType, Rule(new ActiveWarehouseExists())]
        public readonly int $source_warehouse_id,

        #[Required, IntegerType, Rule(new ActiveWarehouseExists()), Different('source_warehouse_id')]
        #[Exists('warehouses', 'id')]
        public readonly int $destination_warehouse_id,

        #[Required, IntegerType, Exists('inventory_items', 'id')]
        public readonly int $inventory_item_id,

        #[Required, IntegerType, Min(1)]
        public readonly int $quantity,

        #[Nullable, StringType, Max(500)]
        public readonly ?string $notes = null,
    ) {}
}