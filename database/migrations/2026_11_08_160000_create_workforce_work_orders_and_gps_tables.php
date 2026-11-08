<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('work_orders')) {
            Schema::create('work_orders', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->string('type')->nullable(); // installation, troubleshooting, maintenance
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->string('status')->default('pending')->index(); // pending, assigned, in_progress, completed, cancelled
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('work_order_checklists')) {
            Schema::create('work_order_checklists', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->unsignedBigInteger('work_order_id')->index();
                $table->string('item');
                $table->boolean('completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gps_logs')) {
            Schema::create('gps_logs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->float('accuracy')->nullable();
                $table->timestamp('logged_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('material_usages')) {
            Schema::table('material_usages', function (Blueprint $table) {
                if (Schema::hasColumn('material_usages', 'installation_id')) {
                    $table->unsignedBigInteger('installation_id')->nullable()->change();
                }
                if (!Schema::hasColumn('material_usages', 'uuid')) {
                    $table->string('uuid')->nullable()->after('id');
                }
                if (!Schema::hasColumn('material_usages', 'work_order_id')) {
                    $table->unsignedBigInteger('work_order_id')->nullable()->after('installation_id');
                }
                if (!Schema::hasColumn('material_usages', 'material_id')) {
                    $table->string('material_id')->nullable()->after('work_order_id');
                }
                if (!Schema::hasColumn('material_usages', 'material_name')) {
                    $table->string('material_name')->nullable()->after('material_id');
                }
                if (!Schema::hasColumn('material_usages', 'serial_number')) {
                    $table->string('serial_number')->nullable()->after('quantity');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
        Schema::dropIfExists('work_order_checklists');
        Schema::dropIfExists('work_orders');
    }
};
