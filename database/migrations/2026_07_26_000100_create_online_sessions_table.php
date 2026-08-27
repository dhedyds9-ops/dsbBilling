<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_key', 255)->unique()->comment('Composite stable key: md5(protocol|nas|username|caller|framed)');
            $table->enum('protocol', ['pppoe', 'hotspot'])->index();
            $table->string('username')->nullable()->index();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->foreignId('nas_device_id')->nullable()->constrained('nas_devices')->nullOnDelete();
            $table->foreignId('radius_nas_id')->nullable()->constrained('radius_nas')->nullOnDelete();
            $table->foreignId('pppoe_user_id')->nullable()->constrained('pppoe_users')->nullOnDelete();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->nullOnDelete();

            $table->string('service')->nullable();
            $table->string('caller_id', 64)->nullable()->index()->comment('MAC / calling station id');
            $table->string('mac_address', 64)->nullable()->index();
            $table->string('address', 45)->nullable()->comment('Framed IP');
            $table->string('server', 64)->nullable()->comment('Hotspot server name');
            $table->string('login_by', 64)->nullable();

            $table->string('uptime', 32)->nullable();
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->unsignedBigInteger('packets_in')->default(0);
            $table->unsignedBigInteger('packets_out')->default(0);
            $table->string('rate_up', 32)->nullable();
            $table->string('rate_down', 32)->nullable();

            $table->string('acct_session_id')->nullable()->index();
            $table->enum('source', ['router_poller', 'radius_accounting', 'manual'])->default('router_poller');
            $table->timestamp('session_started_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();

            $table->timestamps();

            $table->index(['router_id', 'protocol']);
            $table->index(['customer_service_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_sessions');
    }
};
