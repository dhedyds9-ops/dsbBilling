<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acs_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('serial_number')->nullable()->index();
            $table->string('mac_address')->nullable()->index();
            $table->string('oui')->nullable();
            $table->string('manufacturer')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('model')->nullable();
            $table->string('product_class')->nullable();
            $table->string('hardware_version')->nullable();
            $table->string('software_version')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('connection_request_url')->nullable();
            $table->timestamp('last_inform')->nullable();
            $table->timestamp('last_contact')->nullable();
            $table->string('status')->default('offline')->index();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('onu_id')->nullable()->constrained('onus')->nullOnDelete();
            $table->foreignId('olt_id')->nullable()->constrained('olts')->nullOnDelete();
            $table->foreignId('pop_id')->nullable()->constrained('pops')->nullOnDelete();
            $table->foreignId('odp_id')->nullable()->constrained('odps')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('signal')->nullable();
            $table->integer('uptime')->nullable();
            $table->float('cpu')->nullable();
            $table->float('memory')->nullable();
            $table->float('temperature')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acs_devices');
    }
};
