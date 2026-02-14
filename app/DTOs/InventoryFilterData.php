<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;

final class InventoryFilterData extends Data
{
    public function __construct(
        #[Sometimes, Nullable, StringType, Max(255)]
        public ?string $search = null,

        #[Sometimes, Nullable, Numeric, Min(0)]
        public ?float $min_price = null,

        #[Sometimes, Nullable, Numeric, Min(0)]
        public ?float $max_price = null,

        #[Sometimes, Nullable]
        /** @var int[]|null */
        public ?array $warehouse_id = null,

        #[IntegerType, Min(1), Max(100)]
        public int $per_page = 15,

        #[Sometimes, Nullable, StringType]
        public ?string $cursor = null,
    ) {}


    public static function rules(): array
    {
        return [
            'warehouse_id.*' => ['integer', 'exists:warehouses,id'],
        ];
    }


    public function toFilterArray(): array
    {
        $filters = array_filter([
            'search'       => $this->search,
            'min_price'    => $this->min_price,
            'max_price'    => $this->max_price,
            'warehouse_id' => $this->warehouse_id,
        ], static fn(mixed $v): bool => $v !== null && $v !== '');

        ksort($filters);
        
        return $filters;
    }

    public function cacheFingerprint(): string
    {
        return hash('sha256', json_encode($this->toFilterArray()));
    }
}