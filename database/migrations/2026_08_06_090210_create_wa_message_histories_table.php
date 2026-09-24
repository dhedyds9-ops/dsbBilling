<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wa_message_histories', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_number');
            $table->string('recipient_name')->nullable();
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->string('category')->nullable();
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_message_histories');
    }
};
