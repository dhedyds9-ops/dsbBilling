<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Standarisasi variasi penulisan kategori ONU / ONT di tabel items
        DB::table('items')
            ->where(function ($q) {
                $q->where('category', 'ONU / ONT')
                  ->orWhere('category', 'ONU')
                  ->orWhere('category', 'ONT')
                  ->orWhere('category', 'onu/ont')
                  ->orWhere('category', 'onu / ont')
                  ->orWhere('category', 'like', 'ONU%ONT%');
            })
            ->update(['category' => 'ONU/ONT']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
