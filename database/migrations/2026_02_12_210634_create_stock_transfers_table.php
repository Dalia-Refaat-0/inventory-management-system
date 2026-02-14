<?php

declare(strict_types=1);

use App\Enums\TransferStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('destination_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->string('status', 20)
                ->default(TransferStatus::Pending->value);

            $table->string('reference_number', 50)->unique();
            $table->text('notes')->nullable();

            $table->foreignId('transferred_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['source_warehouse_id', 'created_at']);
            $table->index(['destination_warehouse_id', 'created_at']);
            $table->index(['inventory_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};