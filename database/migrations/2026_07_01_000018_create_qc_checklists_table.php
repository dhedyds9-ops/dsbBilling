<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('qc_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspection_id');
            $table->string('item');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('qc_checklists');
    }
};
