<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Additive columns on onus table
        if (Schema::hasTable('onus')) {
            Schema::table('onus', function (Blueprint $table) {
                if (!Schema::hasColumn('onus', 'condition')) {
                    $table->string('condition', 30)->default('good')->index()->after('status');
                }
                if (!Schema::hasColumn('onus', 'audit_status')) {
                    $table->string('audit_status', 30)->default('pending')->index()->after('condition');
                }
                if (!Schema::hasColumn('onus', 'operational_status')) {
                    $table->string('operational_status', 30)->default('unknown')->index()->after('audit_status');
                }
                if (!Schema::hasColumn('onus', 'audited_at')) {
                    $table->timestamp('audited_at')->nullable()->after('provisioned_at');
                }
                if (!Schema::hasColumn('onus', 'audited_by')) {
                    $table->unsignedBigInteger('audited_by')->nullable()->after('audited_at');
                }
            });

            // Normalisasi status legacy tanpa merusak histori
            DB::table('onus')->where('status', 'dismantled_good')->update([
                'status' => 'dismantled',
                'condition' => 'good',
                'operational_status' => 'offline',
            ]);
            DB::table('onus')->where('status', 'dismantled_damaged')->update([
                'status' => 'dismantled',
                'condition' => 'damaged',
                'operational_status' => 'offline',
            ]);
            DB::table('onus')->where('status', 'online')->update([
                'status' => 'active',
                'operational_status' => 'online',
            ]);
        }

        // 2. Create onu_histories table for full ONT lifecycle tracking & idempotency
        if (!Schema::hasTable('onu_histories')) {
            Schema::create('onu_histories', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id', 100)->nullable()->unique();
                $table->unsignedBigInteger('onu_id')->index();
                $table->string('serial_number', 100)->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('old_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->string('condition', 30)->nullable();
                $table->unsignedBigInteger('old_olt_id')->nullable();
                $table->unsignedBigInteger('new_olt_id')->nullable();
                $table->unsignedBigInteger('old_odp_id')->nullable();
                $table->unsignedBigInteger('new_odp_id')->nullable();
                $table->string('activity', 50)->index();
                $table->unsignedBigInteger('technician_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->unsignedBigInteger('work_order_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->json('payload')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Additive source_type and source_id on work_orders table
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'source_type')) {
                    $table->string('source_type', 40)->nullable()->index()->after('type');
                }
                if (!Schema::hasColumn('work_orders', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->index()->after('source_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_orders')) {
            if (Schema::hasColumn('work_orders', 'source_id')) {
                try {
                    Schema::table('work_orders', fn (Blueprint $t) => $t->dropIndex(['source_id']));
                } catch (\Throwable $e) {}
                Schema::table('work_orders', fn (Blueprint $t) => $t->dropColumn('source_id'));
            }
            if (Schema::hasColumn('work_orders', 'source_type')) {
                try {
                    Schema::table('work_orders', fn (Blueprint $t) => $t->dropIndex(['source_type']));
                } catch (\Throwable $e) {}
                Schema::table('work_orders', fn (Blueprint $t) => $t->dropColumn('source_type'));
            }
        }

        // Jangan menghapus tabel onu_histories jika sudah berisi histori operasional!
        if (Schema::hasTable('onu_histories')) {
            $hasOperationalHistory = DB::table('onu_histories')->exists();
            if (!$hasOperationalHistory) {
                Schema::dropIfExists('onu_histories');
            }
        }

        if (Schema::hasTable('onus')) {
            foreach (['operational_status', 'audit_status', 'condition'] as $indexedCol) {
                if (Schema::hasColumn('onus', $indexedCol)) {
                    try {
                        Schema::table('onus', fn (Blueprint $t) => $t->dropIndex([$indexedCol]));
                    } catch (\Throwable $e) {}
                }
            }
            foreach (['audited_by', 'audited_at', 'operational_status', 'audit_status', 'condition'] as $col) {
                if (Schema::hasColumn('onus', $col)) {
                    Schema::table('onus', fn (Blueprint $t) => $t->dropColumn($col));
                }
            }
        }
    }
};
