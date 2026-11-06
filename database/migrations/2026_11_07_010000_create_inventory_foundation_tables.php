<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lengkapi tabel warehouses
        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouses', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('warehouses', 'type')) {
                    $table->string('type', 30)->default('main')->after('code'); // main, branch, transit
                }
                if (!Schema::hasColumn('warehouses', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->index()->after('type');
                }
                if (!Schema::hasColumn('warehouses', 'manager_id')) {
                    $table->unsignedBigInteger('manager_id')->nullable()->index()->after('branch_id');
                }
                if (!Schema::hasColumn('warehouses', 'address')) {
                    $table->text('address')->nullable()->after('manager_id');
                }
                if (!Schema::hasColumn('warehouses', 'city')) {
                    $table->string('city', 100)->nullable()->after('address');
                }
                if (!Schema::hasColumn('warehouses', 'province')) {
                    $table->string('province', 100)->nullable()->after('city');
                }
                if (!Schema::hasColumn('warehouses', 'postal_code')) {
                    $table->string('postal_code', 20)->nullable()->after('province');
                }
                if (!Schema::hasColumn('warehouses', 'phone')) {
                    $table->string('phone', 30)->nullable()->after('postal_code');
                }
                if (!Schema::hasColumn('warehouses', 'email')) {
                    $table->string('email', 100)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('warehouses', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('email');
                }
                if (!Schema::hasColumn('warehouses', 'allows_returns')) {
                    $table->boolean('allows_returns')->default(true)->after('is_active');
                }
                if (!Schema::hasColumn('warehouses', 'capacity')) {
                    $table->integer('capacity')->nullable()->after('allows_returns');
                }
                if (!Schema::hasColumn('warehouses', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('capacity');
                }
                if (!Schema::hasColumn('warehouses', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
            });
        }

        // 2. Lengkapi tabel items (Master Barang)
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('items', 'sku')) {
                    $table->string('sku', 80)->nullable()->unique()->after('uuid');
                }
                if (!Schema::hasColumn('items', 'name')) {
                    $table->string('name')->nullable()->after('sku');
                }
                if (!Schema::hasColumn('items', 'category')) {
                    $table->string('category', 80)->nullable()->index()->after('name');
                }
                if (!Schema::hasColumn('items', 'unit')) {
                    $table->string('unit', 30)->default('pcs')->after('category');
                }
                if (!Schema::hasColumn('items', 'brand')) {
                    $table->string('brand', 80)->nullable()->after('unit');
                }
                if (!Schema::hasColumn('items', 'model')) {
                    $table->string('model', 80)->nullable()->after('brand');
                }
                if (!Schema::hasColumn('items', 'description')) {
                    $table->text('description')->nullable()->after('model');
                }
                if (!Schema::hasColumn('items', 'min_stock')) {
                    $table->integer('min_stock')->default(5)->after('description');
                }
                if (!Schema::hasColumn('items', 'is_serialized')) {
                    $table->boolean('is_serialized')->default(false)->index()->after('min_stock');
                }
                if (!Schema::hasColumn('items', 'is_reusable')) {
                    $table->boolean('is_reusable')->default(false)->after('is_serialized');
                }
                if (!Schema::hasColumn('items', 'cost_price')) {
                    $table->decimal('cost_price', 15, 2)->nullable()->after('is_reusable');
                }
                if (!Schema::hasColumn('items', 'selling_price')) {
                    $table->decimal('selling_price', 15, 2)->nullable()->after('cost_price');
                }
                if (!Schema::hasColumn('items', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index()->after('selling_price');
                }
                if (!Schema::hasColumn('items', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->index()->after('is_active');
                }
                if (!Schema::hasColumn('items', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('branch_id');
                }
                if (!Schema::hasColumn('items', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('items', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        // 3. Buat tabel item_serials (Pelacakan Serial Number per Unit)
        if (!Schema::hasTable('item_serials')) {
            Schema::create('item_serials', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('item_id')->index();
                $table->string('serial_number', 100)->index();
                $table->string('mac_address', 50)->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->unsignedBigInteger('custodian_id')->nullable()->index(); // User ID Reseller / Teknisi
                $table->string('custodian_type', 30)->nullable(); // reseller, technician
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('installation_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->string('status', 40)->default('in_warehouse')->index(); 
                // in_warehouse, in_custody_reseller, in_custody_technician, installed, returned_check, damaged, scrapped
                $table->string('condition', 30)->default('good'); // good, needs_check, damaged
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 4. Buat tabel inventory_stocks (Saldo Fisik per Gudang)
        if (!Schema::hasTable('inventory_stocks')) {
            Schema::create('inventory_stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->decimal('quantity', 12, 2)->default(0);
                $table->decimal('reserved_quantity', 12, 2)->default(0);
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->timestamps();

                $table->unique(['warehouse_id', 'item_id']);
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 5. Buat tabel inventory_stock_mutations (Immutable Ledger & Kartu Stok)
        if (!Schema::hasTable('inventory_stock_mutations')) {
            Schema::create('inventory_stock_mutations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('mutation_number', 50)->unique()->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->string('type', 40)->index();
                // inbound, outbound_dispatch, transfer_out, transfer_in, usage, return_good, return_damaged, adjustment_in, adjustment_out
                $table->decimal('qty_in', 12, 2)->default(0);
                $table->decimal('qty_out', 12, 2)->default(0);
                $table->decimal('balance_before', 12, 2)->default(0);
                $table->decimal('balance_after', 12, 2)->default(0);
                $table->string('reference_type', 80)->nullable()->index();
                $table->unsignedBigInteger('reference_id')->nullable()->index();
                $table->unsignedBigInteger('item_serial_id')->nullable()->index();
                $table->string('batch_number', 80)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->timestamp('created_at')->index();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 6. Buat tabel inventory_inbounds (Penerimaan Barang Masuk)
        if (!Schema::hasTable('inventory_inbounds')) {
            Schema::create('inventory_inbounds', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('inbound_number', 50)->unique()->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('source_type', 40)->default('supplier'); // supplier, purchase, initial_stock, other
                $table->string('supplier_name')->nullable();
                $table->string('reference_doc', 100)->nullable(); // Nomor Faktur / PO
                $table->date('received_date')->index();
                $table->string('status', 30)->default('received')->index(); // draft, received, cancelled
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('received_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            });
        }

        // 7. Buat tabel inventory_inbound_items (Detail Barang Masuk)
        if (!Schema::hasTable('inventory_inbound_items')) {
            Schema::create('inventory_inbound_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inbound_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('unit_cost', 15, 2)->nullable();
                $table->decimal('subtotal', 15, 2)->nullable();
                $table->json('serials')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('inbound_id')->references('id')->on('inventory_inbounds')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_inbound_items');
        Schema::dropIfExists('inventory_inbounds');
        Schema::dropIfExists('inventory_stock_mutations');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('item_serials');

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $columns = [
                    'uuid', 'sku', 'name', 'category', 'unit', 'brand', 'model',
                    'description', 'min_stock', 'is_serialized', 'is_reusable',
                    'cost_price', 'selling_price', 'is_active', 'branch_id',
                    'created_by', 'updated_by', 'deleted_at'
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $columns = [
                    'uuid', 'type', 'branch_id', 'manager_id', 'address', 'city',
                    'province', 'postal_code', 'phone', 'email', 'is_active',
                    'allows_returns', 'capacity', 'latitude', 'longitude'
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('warehouses', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
