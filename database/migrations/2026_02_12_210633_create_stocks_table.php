<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(10);

            $table->timestamps();

            $table->unique(
                ['warehouse_id', 'inventory_item_id'],
                'stocks_warehouse_item_unique'
            );

            $table->index('quantity');
            $table->index('created_at');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};