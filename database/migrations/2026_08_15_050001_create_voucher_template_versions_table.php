<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_template_id')->constrained('voucher_templates')->cascadeOnDelete();
            $table->integer('version');
            $table->longText('template_code');
            $table->longText('css_code')->nullable();
            $table->longText('js_code')->nullable();
            $table->json('variables_schema')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['voucher_template_id', 'version']);
            $table->index('version');
            $table->index('created_by');
        });
    }
};
