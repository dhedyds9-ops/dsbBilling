<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('subject', 'title');
            $table->uuid('uuid')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('title', 'subject');
            $table->dropColumn(['uuid', 'category', 'resolved_at']);
        });
    }
};
