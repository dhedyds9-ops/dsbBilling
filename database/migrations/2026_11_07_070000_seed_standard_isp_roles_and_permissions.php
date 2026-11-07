<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\Auth\PermissionRegistry;

return new class extends Migration
{
    /**
     * Run the migrations to seed all 10 standard ISP roles and granular permissions.
     */
    public function up(): void
    {
        PermissionRegistry::syncAllPermissionsToDatabase();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
