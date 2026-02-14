<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Warehouse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveWarehouseExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Warehouse::where('id', $value)->where('is_active', true)->exists()) {
            $fail('The selected warehouse does not exist or is not active.');
        }
    }
}