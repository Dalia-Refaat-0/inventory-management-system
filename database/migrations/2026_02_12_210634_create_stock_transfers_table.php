<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TransferStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_warehouse_id')
                    ->constrained('warehouses')->restrictOnDelete();

            $table->foreignId('destination_warehouse_id')
                    ->constrained('warehouses')->restrictOnDelete();

            $table->foreignId('inventory_item_id')
                    ->constrained()->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->enum('status', TransferStatus::values())
                    ->default(TransferStatus::Pending->value);  

            $table->string('reference_number', 50)->unique();

            $table->text('notes')->nullable();

            $table->foreignId('transferred_by')
                    ->nullable()->constrained('users')->nullOnDelete();
                
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};