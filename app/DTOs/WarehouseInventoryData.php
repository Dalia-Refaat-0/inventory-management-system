<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Rules\ActiveWarehouseExists;
use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

final class WarehouseInventoryData extends Data
{
    public function __construct(
        #[Required, IntegerType, Rule(new ActiveWarehouseExists())]
        public int $warehouseId,

        #[Required, IntegerType, Between(1, 100)]
        #[MapInputName('per_page')]
        public int $perPage = 15,

        #[Nullable, StringType, Max(255), Regex('/^[A-Za-z0-9+\/=_-]+$/')]
        public ?string $cursor = null,
    ) {}

    public static function fromRequest(Request $request, mixed $warehouseId): self
    {
        $data = [
            'warehouseId' => (int) $warehouseId,
            'per_page'    => $request->input('per_page', 15),
            'cursor'      => $request->input('cursor'),
        ];
        
        return self::from(self::validate($data));
    }
}