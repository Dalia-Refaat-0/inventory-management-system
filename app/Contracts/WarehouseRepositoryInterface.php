<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    public function getActive(bool $withCounts = false): Collection;

}