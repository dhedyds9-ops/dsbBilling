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
        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('device_id', 120)->index();
                $table->string('fcm_token', 500)->index();
                $table->string('platform', 30)->default('android');
                $table->string('app_version', 50)->nullable();
                $table->string('device_name', 150)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'device_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
