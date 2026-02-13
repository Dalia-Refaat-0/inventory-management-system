<?php

declare(strict_types=1);

namespace App\Casts;

use App\ValueObjects\SKU;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<SKU, SKU|string>
 */
class SKUCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?SKU
    {
        if ($value === null) {
            return null;
        }

        return SKU::fromString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof SKU) {
            return $value->value();
        }

        if (is_string($value)) {
            return SKU::fromString($value)->value();
        }

        throw new InvalidArgumentException('Value must be SKU instance or valid SKU string.');
    }
}