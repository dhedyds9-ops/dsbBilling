<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_template_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_template_id')->constrained('voucher_templates')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->integer('size_kb')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('type');
            $table->index('voucher_template_id');
        });
    }
};
