<?php

declare(strict_types=1);

namespace App\Support;

use App\Contracts\StockTransferRepositoryInterface;
use Illuminate\Support\Str;

class TransferReferenceGenerator
{
    public function __construct(
        private readonly StockTransferRepositoryInterface $repository
    ) {}

    public function generate(): string
    {
        do {
            $reference = sprintf('TRF-%s-%s', now()->format('Ymd'), strtoupper(Str::random(8)));
        } while ($this->repository->referenceExists($reference));

        return $reference;
    }
}