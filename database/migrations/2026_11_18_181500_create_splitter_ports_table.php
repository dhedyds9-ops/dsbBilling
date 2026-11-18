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
        if (!Schema::hasTable('splitter_ports')) {
            Schema::create('splitter_ports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('splitter_id')->constrained('splitters')->cascadeOnDelete();
                $table->unsignedSmallInteger('port_number');
                $table->string('port_type', 10)->default('output'); // 'input', 'output'
                $table->string('status', 20)->default('available'); // 'available', 'used', 'reserved', 'fault'
                $table->decimal('attenuation_db', 5, 2)->nullable();
                
                // Target Downstream Connection (Polymorphic: Odp, Onu, AccessPoint, DistributionBox, JointClosure, Splitter)
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->unsignedSmallInteger('target_port_number')->nullable();
                $table->string('target_label')->nullable();

                // Direct Customer Link (if target is ONU or dedicated subscriber drop)
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                
                // Uplink Source Reference (for input ports: Olt, Odc, Splitter, PonPort)
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->unsignedSmallInteger('source_port_number')->nullable();
                
                // Physical Core / Drop Identifiers
                $table->string('core_color', 30)->nullable();
                $table->text('notes')->nullable();
                
                // Multi-tenancy & Scoping
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->softDeletes();
                $table->timestamps();

                $table->unique(['splitter_id', 'port_type', 'port_number'], 'idx_splitter_port_unique');
                $table->index(['target_type', 'target_id'], 'idx_splitter_port_target');
                $table->index(['source_type', 'source_id'], 'idx_splitter_port_source');
                $table->index('status', 'idx_splitter_port_status');
                $table->index('branch_id', 'idx_splitter_port_branch');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('splitter_ports');
    }
};
