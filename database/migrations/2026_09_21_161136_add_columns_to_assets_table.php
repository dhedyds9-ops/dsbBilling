<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            
            // These might be strings or foreign keys. The model uses `_id` so we'll use unsignedBigInteger.
            // But we don't strictly constrain them if the tables don't exist.
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->string('rack_position')->nullable();
            
            $table->string('status')->nullable();
            $table->string('condition')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            
            $table->integer('warranty_months')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('parent_asset_id')->nullable();
            
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->string('assigned_to_type')->nullable();
            $table->timestamp('assigned_at')->nullable();
            
            $table->unsignedBigInteger('installation_id')->nullable();
            $table->timestamp('installed_at')->nullable();
            
            $table->string('barcode_path')->nullable();
            $table->string('qrcode_path')->nullable();
            $table->json('custom_fields')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'name', 'description', 'type', 'category_id', 'warehouse_id',
                'location_id', 'rack_id', 'rack_position', 'status', 'condition',
                'serial_number', 'mac_address', 'purchase_price', 'purchase_date',
                'warranty_months', 'warranty_start_date', 'warranty_end_date',
                'vendor_id', 'parent_asset_id', 'assigned_to_id', 'assigned_to_type',
                'assigned_at', 'installation_id', 'installed_at', 'barcode_path',
                'qrcode_path', 'custom_fields'
            ]);
        });
    }
};
