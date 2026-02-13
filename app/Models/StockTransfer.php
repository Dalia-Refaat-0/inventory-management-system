<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int            $id
 * @property int            $source_warehouse_id
 * @property int            $destination_warehouse_id
 * @property int            $inventory_item_id
 * @property int            $quantity
 * @property TransferStatus $status
 * @property string         $reference_number
 * @property string|null    $notes
 * @property int|null       $transferred_by
 */
class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_warehouse_id',
        'destination_warehouse_id',
        'inventory_item_id',
        'quantity',
        'status',
        'reference_number',
        'notes',
        'transferred_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'status'   => TransferStatus::class,
    ];

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function transferredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

}