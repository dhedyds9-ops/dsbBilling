<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('cycle_type'); // monthly, quarterly, yearly
            $table->timestamp('cycle_start')->nullable();
            $table->timestamp('cycle_end')->nullable();
            $table->timestamp('invoice_due_days')->default(7); // Jatuh tempo n hari setelah invoice dibuat
            $table->string('status')->default('active'); // active, closed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('cycle_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
    }
};
