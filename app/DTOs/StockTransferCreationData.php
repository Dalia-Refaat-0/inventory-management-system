<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TransferStatus;
use App\Rules\ActiveWarehouseExists;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

final class StockTransferCreationData extends Data
{
    public function __construct(
        #[Required, IntegerType, Rule(new ActiveWarehouseExists())]
        public readonly int $source_warehouse_id,

        #[Required, IntegerType, Different('source_warehouse_id'), Rule(new ActiveWarehouseExists())]
        public readonly int $destination_warehouse_id,

        #[Required, IntegerType, Exists('inventory_items', 'id')]
        public readonly int $inventory_item_id,

        #[Required, IntegerType, Min(1)]
        public readonly int $quantity,

        #[Required]
        public readonly TransferStatus $status,
        
        #[Required, StringType, Max(50), Unique('stock_transfers', 'reference_number')]
        public readonly string $reference_number,
        
        #[Required, IntegerType, Exists('users', 'id')]
        public readonly int $transferred_by,

        #[Nullable, StringType, Max(1000)]
        public readonly ?string $notes,

        #[Required, Date]
        public readonly CarbonImmutable $transferred_at,
    ) {}

    public static function fromTransfer(
        TransferStockData $data,
        TransferStatus    $status,
        string            $referenceNumber,
        int               $transferredBy,
    ): self {
        return self::from([
            'source_warehouse_id'      => $data->source_warehouse_id,
            'destination_warehouse_id' => $data->destination_warehouse_id,
            'inventory_item_id'        => $data->inventory_item_id,
            'quantity'                 => $data->quantity,
            'status'                   => $status,
            'reference_number'         => $referenceNumber,
            'transferred_by'           => $transferredBy,
            'notes'                    => $data->notes,
            'transferred_at'           => CarbonImmutable::now(),
        ]);
    }
}