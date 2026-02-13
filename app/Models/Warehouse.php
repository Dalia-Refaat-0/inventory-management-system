<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\WarehouseBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property int        $id
 * @property string     $name
 * @property Point|null $location
 * @property string     $address_text
 * @property bool       $is_active
 * @property Collection $stocks
 * @property Collection $outgoingTransfers
 * @property Collection $incomingTransfers
 *
 * @method static WarehouseBuilder query()
 */
class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'address_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'location'  => Point::class,
    ];

    public function newEloquentBuilder($query): WarehouseBuilder
    {
        return new WarehouseBuilder($query);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }
}