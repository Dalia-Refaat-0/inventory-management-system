<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\StockBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int           $id
 * @property int           $warehouse_id
 * @property int           $inventory_item_id
 * @property int           $quantity
 * @property int           $low_stock_threshold
 * @property Warehouse     $warehouse
 * @property InventoryItem $inventoryItem
 *
 * @method static StockBuilder query()
 */
class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'quantity'            => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function newEloquentBuilder($query): StockBuilder
    {
        return new StockBuilder($query);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function hasEnoughStock(int $requestedQuantity): bool
    {
        return $this->quantity >= $requestedQuantity;
    }
}