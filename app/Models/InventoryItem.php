<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SKUCast;
use App\ValueObjects\SKU;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int        $id
 * @property string     $name
 * @property SKU        $sku
 * @property float      $price
 * @property Collection $stocks
 * @property Collection $transfers
 * @property-read int   $total_stock
 */
class InventoryItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sku'   => SKUCast::class,
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }
}