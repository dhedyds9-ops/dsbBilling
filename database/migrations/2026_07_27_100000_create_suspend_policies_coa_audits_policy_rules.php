<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============ TABEL 1: SUSPEND POLICY (bukan hardcoded 128k!) ============
        Schema::create('suspend_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                 // "Default Suspend 128k", "Strict 64k Isolir", "Hanya Redirect Portal"
            $table->string('slug', 60)->unique();        // default_128k, strict_64k, redirect_only, disconnect_only
            $table->text('description')->nullable();
            $table->string('action_type', 30);           // Enum SuspendPolicyAction: rate_limit, redirect_only, disconnect, disable_secret

            // ---- Parameter rate limit (jika action_type = rate_limit) ----
            $table->unsignedInteger('rate_download_kbps')->nullable(); // 128 kbps = 128
            $table->unsignedInteger('rate_upload_kbps')->nullable();
            $table->unsignedInteger('burst_download_kbps')->nullable();
            $table->unsignedInteger('burst_upload_kbps')->nullable();
            $table->unsignedInteger('burst_threshold_kbps')->nullable();
            $table->unsignedInteger('burst_time_seconds')->nullable();

            // ---- Parameter redirect + address list ----
            $table->string('address_list', 60)->nullable();      // default: DSBILLING_ISOLIR
            $table->text('redirect_url')->nullable();             // https://pay.isp-anda.co.id?from=suspend

            // ---- Default policy ----
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            // Foreign keys
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Default seed di migration (jangan lupa php artisan migrate:refresh will rerun)
        $suspendPolicies = [
            [
                'name' => 'Default Suspend 128k',
                'slug' => 'default_128k',
                'description' => 'Rate limit 128 kbps untuk user isolir (default enterprise ISP)',
                'action_type' => 'rate_limit',
                'rate_download_kbps' => 128,
                'rate_upload_kbps' => 128,
                'address_list' => 'DSBILLING_ISOLIR',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Strict Isolir 64k',
                'slug' => 'strict_64k',
                'description' => 'Rate 64 kbps - hampir tidak bisa browsing, pakai untuk user terlambat > 14 hari',
                'action_type' => 'rate_limit',
                'rate_download_kbps' => 64,
                'rate_upload_kbps' => 64,
                'address_list' => 'DSBILLING_STRICT_ISOLIR',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '512k Portal Payment Only',
                'slug' => 'portal_512k',
                'description' => '512k cukup untuk buka portal pembayaran + WhatsApp',
                'action_type' => 'rate_limit',
                'rate_download_kbps' => 512,
                'rate_upload_kbps' => 512,
                'address_list' => 'DSBILLING_ISOLIR',
                'redirect_url' => 'https://pay.isp-anda.co.id',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Redirect Hanya',
                'slug' => 'redirect_only',
                'description' => 'Bandwidth TETAP NORMAL tapi semua HTTP redirect ke portal bayar. User kecewa ringan.',
                'action_type' => 'redirect_only',
                'address_list' => 'DSBILLING_REDIRECT',
                'redirect_url' => 'https://pay.isp-anda.co.id/info-tagihan',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kick Disconnect',
                'slug' => 'disconnect_only',
                'description' => 'Packet of Disconnect langsung, tidak kasih rate limit',
                'action_type' => 'disconnect',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Disable Secret Permanen',
                'slug' => 'disable_secret',
                'description' => 'User PELANGGARAN BERAT: disable PPP secret (bisa aktif jika admin aktifkan manual)',
                'action_type' => 'disable_secret',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($suspendPolicies as $policy) {
            DB::table('suspend_policies')->insertOrIgnore($policy);
        }

        // Tambahkan default_suspend_policy_id FK ke service_profiles
        if (!Schema::hasColumn('service_profiles', 'suspend_policy_id')) {
            Schema::table('service_profiles', function (Blueprint $table) {
                $table->foreignId('suspend_policy_id')
                    ->nullable()
                    ->after('package_id')
                    ->constrained('suspend_policies')
                    ->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('customer_services', 'overridden_suspend_policy_id')) {
            Schema::table('customer_services', function (Blueprint $table) {
                $table->foreignId('overridden_suspend_policy_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('suspend_policies')
                    ->nullOnDelete();
            });
        }

        // ============ TABEL 2: COA AUDIT LOG (Full audit trail) ============
        Schema::create('radius_coa_audits', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // COA Meta
            $table->string('coa_type', 40)->index();       // Enum CoaType: suspend/reactivate/bandwidth_change/...
            $table->string('state', 30)->index();            // Enum CoaAuditState: created/queued/sent/success/timeout/retry/failed/nak

            // Attempt tracking (max 3 retry)
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);

            // Data payload
            $table->json('identifiers');                     // {User-Name, Calling-Station-Id, Framed-IP}
            $table->json('attributes_to_change');            // {MikroTik-Rate-Limit: ..., Address-List: ...}
            $table->json('coa_result')->nullable();          // {success, coa_code, resp_hex, error}
            $table->text('last_error_message')->nullable();

            // Foreign keys untuk backreference
            $table->foreignId('radius_nas_id')->nullable()->constrained('radius_nas')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->foreignId('pppoe_user_id')->nullable()->constrained('pppoe_users')->index()->cascadeOnDelete();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->index()->nullOnDelete();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->index()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();

            // Timestamp state machine
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('queued_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('success_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();

            // Performance index
            $table->index(['state', 'created_at']);
            $table->index(['coa_type', 'state']);
            $table->index(['pppoe_user_id', 'created_at']);
        });

        // ============ TABEL 3: RADIUS AUTH POLICY RULES (Configurable rules, TAMBAHAN FLEXIBLE) ============
        Schema::create('radius_auth_policy_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_class', 200);                // App\Services\ISP\Radius\Policies\InvoiceNotOverduePolicy
            $table->string('rule_name', 80);
            $table->text('description')->nullable();
            $table->unsignedInteger('priority')->default(10); // Order of execution (ascending)
            $table->boolean('enabled')->default(true)->index();
            $table->json('config')->nullable();               // {grace_days: 3, whitelist_groups: [vip]}
            $table->string('fail_action', 30)->default('reject'); // reject/warn/redirect_only
            $table->text('fail_message')->nullable();         // "Tagihan Anda belum dibayar. Silakan bayar via..."

            $table->timestamps();
        });

        // Seed default 8 policy rule (sesuai urutan Policy Engine Chain)
        $seed = [
            ['rule_class' => \App\Services\ISP\Radius\Policies\CustomerActivePolicy::class, 'rule_name' => 'Customer Status Aktif', 'priority' => 1, 'fail_message' => 'Akun pelanggan dinonaktifkan, hubungi CS'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\PackageActivePolicy::class, 'rule_name' => 'Paket Internet Aktif', 'priority' => 2, 'fail_message' => 'Paket layanan Anda sudah di-nonaktifkan'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\NotExpiredPolicy::class, 'rule_name' => 'Masa Aktif Berlalu', 'priority' => 3, 'fail_message' => 'Masa aktif paket berakhir, perpanjang sekarang!'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\InvoiceNotOverduePolicy::class, 'rule_name' => 'Tidak Ada Tagihan Overdue', 'priority' => 10, 'config' => json_encode(['grace_days' => 3, 'strict_days_after_overdue' => 14]), 'fail_message' => 'Tagihan Anda melewati batas waktu, silakan lakukan pembayaran'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\NasAllowedPolicy::class, 'rule_name' => 'Lokasi POP Diijinkan', 'priority' => 15, 'fail_message' => 'Anda tidak diijinkan login dari POP ini'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\QuotaAvailablePolicy::class, 'rule_name' => 'Kuota Masih Tersedia (FUP)', 'priority' => 20, 'fail_message' => 'Kuota FUP bulanan Anda sudah habis'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\AccessHoursPolicy::class, 'rule_name' => 'Jam Akses Diijinkan', 'priority' => 25, 'config' => json_encode(['timezone' => 'Asia/Jakarta']), 'fail_message' => 'Akses internet hanya diijinkan jam 06:00 - 24:00'],
            ['rule_class' => \App\Services\ISP\Radius\Policies\IpBlacklistPolicy::class, 'rule_name' => 'IP / MAC Tidak Masuk Blacklist', 'priority' => 30, 'fail_message' => 'MAC address / IP Anda tercatat di blacklist karena pelanggaran'],
        ];
        foreach ($seed as $row) {
            $row += ['enabled' => true, 'created_at' => now(), 'updated_at' => now(), 'fail_action' => 'reject'];
            DB::table('radius_auth_policy_rules')->insertOrIgnore($row);
        }

        // Tambahkan kolom state ke online_sessions (state machine)
        if (!Schema::hasColumn('online_sessions', 'radius_state')) {
            Schema::table('online_sessions', function (Blueprint $table) {
                $table->string('radius_state', 40)
                    ->default('connecting')
                    ->after('protocol')
                    ->index();
                $table->timestamp('state_changed_at')->nullable()->after('last_seen_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('online_sessions', function (Blueprint $table) {
            $table->dropColumn(['radius_state', 'state_changed_at']);
        });
        Schema::dropIfExists('radius_auth_policy_rules');
        Schema::dropIfExists('radius_coa_audits');
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('overridden_suspend_policy_id');
        });
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suspend_policy_id');
        });
        Schema::dropIfExists('suspend_policies');
    }
};
