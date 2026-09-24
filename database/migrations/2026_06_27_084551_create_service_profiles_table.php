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
        Schema::create('service_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_profile_type_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // Speed & Bandwidth
            $table->integer('download_speed')->nullable();
            $table->integer('upload_speed')->nullable();
            $table->integer('burst_download')->nullable();
            $table->integer('burst_upload')->nullable();
            $table->integer('priority')->default(8);
            // Quota
            $table->decimal('quota', 15, 2)->nullable();
            $table->string('quota_unit')->default('GB'); // GB, MB, Unlimited
            // Session
            $table->string('session_timeout')->nullable();
            $table->string('idle_timeout')->nullable();
            $table->string('cookie_timeout')->nullable();
            $table->string('lease_time')->nullable();
            // Network
            $table->string('pool_name')->nullable();
            $table->string('gateway')->nullable();
            $table->string('dns')->nullable();
            $table->string('subnet')->nullable();
            // Queue
            $table->string('queue_type')->nullable();
            $table->string('mikrotik_profile')->nullable();
            $table->string('radius_profile')->nullable();
            $table->string('framed_ip')->nullable();
            // Voucher & Price
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('validity_days')->nullable();
            $table->integer('validity_hours')->nullable();
            $table->integer('max_devices')->nullable();
            // Status
            $table->string('status')->default('active');
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_profiles');
    }
};
