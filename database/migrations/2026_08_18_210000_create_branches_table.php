<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel branches sebagai business entity/organizational scope.
 *
 * PRINSIP:
 *  - Branch adalah business scope, BUKAN role
 *  - Jangan membuat role branch_manager atau branch_operator
 *  - User (manager/reseller) memiliki scope ke branch tertentu
 *
 * Relasi yang akan dibangun:
 *  branches.id ← service_profiles.branch_id
 *  branches.id ← users.branch_id (scope)
 *  branches.id ← members.branch_id (customer branch)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique()->comment('Kode cabang, misal: CBG-01');
            $table->string('name')->comment('Nama cabang, misal: Cabang Sukabumi');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('code');
        });

        // Tambahkan FK constraint yang proper ke service_profiles.branch_id
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });

        // Add branch_id scope to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::dropIfExists('branches');
    }
};
