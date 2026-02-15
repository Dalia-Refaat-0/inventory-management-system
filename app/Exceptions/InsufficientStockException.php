<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

final class InsufficientStockException extends Exception
{
    public function __construct(
        private readonly int $requested,
        private readonly int $available
    ) {
        parent::__construct("Insufficient stock for transfer.");
    }


    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'quantity' => [
                    "You requested {$this->requested} units, but only {$this->available} are available."
                ]
            ]
        ], 422);
    }
}