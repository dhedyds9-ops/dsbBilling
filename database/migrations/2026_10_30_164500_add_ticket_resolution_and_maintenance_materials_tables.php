<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'resolution_notes')) {
                    $table->text('resolution_notes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('tickets', 'due_date')) {
                    $table->date('due_date')->nullable()->after('resolved_at');
                }
            });
        }

        if (Schema::hasTable('support_maintenances')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('support_maintenances', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                }
            });
        }

        if (!Schema::hasTable('support_maintenance_materials')) {
            Schema::create('support_maintenance_materials', function (Blueprint $table) {
                $table->id();
                $table->string('sku', 80)->nullable();
                $table->string('name');
                $table->string('category', 80)->nullable();
                $table->integer('stock')->default(0);
                $table->integer('min_stock')->default(5);
                $table->string('unit', 30)->default('pcs');
                $table->string('location')->nullable()->default('Gudang Utama');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('support_maintenance_usages')) {
            Schema::create('support_maintenance_usages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('maintenance_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->unsignedBigInteger('material_id')->nullable()->index();
                $table->string('material_name');
                $table->integer('quantity')->default(1);
                $table->string('unit', 30)->default('pcs');
                $table->unsignedBigInteger('technician_id')->nullable()->index();
                $table->string('technician_name')->nullable();
                $table->date('used_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_maintenance_usages');
        Schema::dropIfExists('support_maintenance_materials');

        if (Schema::hasTable('support_maintenances') && Schema::hasColumn('support_maintenances', 'branch_id')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('tickets', 'resolution_notes')) {
                    $drop[] = 'resolution_notes';
                }
                if (Schema::hasColumn('tickets', 'due_date')) {
                    $drop[] = 'due_date';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};
