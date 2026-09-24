<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('material_consumptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('inventory_item_id');
            $table->integer('quantity');
            $table->text('notes')->default('');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('material_consumptions');
    }
};
