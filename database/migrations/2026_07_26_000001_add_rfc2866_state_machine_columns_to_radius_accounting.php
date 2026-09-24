<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function columnExists(string $table, string $col): bool
    {
        try {
            return Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function indexExists(string $table, string $indexNameLower): bool
    {
        try {
            $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=?", [$table]);
            foreach ($indexes as $i) {
                if (strtolower($i->name ?? '') === $indexNameLower) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
        }
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $existing = collect($sm->listTableIndexes($table))->keys()->map(fn($k) => strtolower($k));
            return $existing->contains($indexNameLower);
        } catch (\Throwable $e) {
        }
        return false;
    }

    private function fkExists(string $table, string $fkNameLower): bool
    {
        try {
            $rows = DB::select("PRAGMA foreign_key_list({$table})");
            foreach ($rows as $r) {
                if (strtolower($r->id ?? '') === $fkNameLower || strtolower($r->from ?? '') === $fkNameLower) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
        }
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $existing = collect($sm->listTableForeignKeys($table))->keys()->map(fn($k) => strtolower($k));
            return $existing->contains($fkNameLower);
        } catch (\Throwable $e) {
        }
        return false;
    }

    public function up(): void
    {
        Schema::table('radius_accounting', function (Blueprint $table) {
            $addUnique = false;

            if (!$this->columnExists('radius_accounting', 'acct_status_type')) {
                $table->enum('acct_status_type', [
                    'start',
                    'stop',
                    'interim',
                    'accounting_on',
                    'accounting_off',
                    'failed',
                ])->nullable()->after('acct_session_id')->index();
            }
            if (!$this->columnExists('radius_accounting', 'acct_unique_session_id')) {
                $table->string('acct_unique_session_id')
                    ->nullable()
                    ->after('acct_session_id')
                    ->comment('RFC 2866 Acct-Unique-Session-Id untuk deduplikasi global');
            }
            if (!$this->columnExists('radius_accounting', 'acct_input_gigawords')) {
                $table->bigInteger('acct_input_gigawords')->default(0)->after('acct_input_octets');
            }
            if (!$this->columnExists('radius_accounting', 'acct_output_gigawords')) {
                $table->bigInteger('acct_output_gigawords')->default(0)->after('acct_output_octets');
            }
            if (!$this->columnExists('radius_accounting', 'acct_delay_time')) {
                $table->integer('acct_delay_time')->default(0)->after('acct_session_time')
                    ->comment('Detik delay dari NAS ke ingest');
            }
            if (!$this->columnExists('radius_accounting', 'calling_station_id')) {
                $table->string('calling_station_id')->nullable()->after('nas_port_id')
                    ->comment('MAC Address pemanggil');
            }
            if (!$this->columnExists('radius_accounting', 'called_station_id')) {
                $table->string('called_station_id')->nullable()->after('calling_station_id')
                    ->comment('MAC / SSID tujuan');
            }
            if (!$this->columnExists('radius_accounting', 'connect_info')) {
                $table->string('connect_info')->nullable()->after('called_station_id')
                    ->comment('Connect-Info (rate, modulation)');
            }
            if (!$this->columnExists('radius_accounting', 'terminate_cause_id')) {
                $table->smallInteger('terminate_cause_id')->nullable()->after('acct_terminate_cause')
                    ->comment('RFC 2866 numeric terminate cause 1..19');
            }
            if (!$this->columnExists('radius_accounting', 'raw_payload')) {
                $table->json('raw_payload')->nullable()->after('customer_service_id')
                    ->comment('Original payload untuk debug / replay');
            }
            if (!$this->columnExists('radius_accounting', 'ingest_source')) {
                $table->string('ingest_source')->default('free_radius_rest')
                    ->after('raw_payload')
                    ->comment('free_radius_rest, mikrotik_api_poller, detail_log_parser');
            }
            if (!$this->columnExists('radius_accounting', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('ingest_source')
                    ->comment('Waktu diterima oleh controller ingest (bukan waktu DB)');
            }
            if (!$this->columnExists('radius_accounting', 'radius_nas_id')) {
                $addUnique = true;
                $table->foreignId('radius_nas_id')
                    ->nullable()
                    ->after('nas_ip_address')
                    ->constrained('radius_nas')
                    ->nullOnDelete();
            }
            if (!$this->columnExists('radius_accounting', 'nas_device_id')) {
                $addUnique = true;
                $table->foreignId('nas_device_id')
                    ->nullable()
                    ->after('radius_nas_id')
                    ->constrained('nas_devices')
                    ->nullOnDelete();
            }

            $idxName = 'radius_accounting_session_status_unique';
            if (!$this->indexExists('radius_accounting', strtolower($idxName)) && $this->columnExists('radius_accounting', 'acct_status_type')) {
                try {
                    $table->unique(['acct_session_id', 'acct_status_type'], $idxName);
                } catch (\Throwable $e) {
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('radius_accounting', function (Blueprint $table) {
            if ($this->fkExists('radius_accounting', 'radius_accounting_radius_nas_id_foreign')
                || $this->columnExists('radius_accounting', 'radius_nas_id')) {
                try {
                    $table->dropForeign(['radius_nas_id']);
                } catch (\Throwable $e) {
                }
            }
            if ($this->fkExists('radius_accounting', 'radius_accounting_nas_device_id_foreign')
                || $this->columnExists('radius_accounting', 'nas_device_id')) {
                try {
                    $table->dropForeign(['nas_device_id']);
                } catch (\Throwable $e) {
                }
            }

            if ($this->indexExists('radius_accounting', 'radius_accounting_session_status_unique')) {
                try {
                    $table->dropUnique('radius_accounting_session_status_unique');
                } catch (\Throwable $e) {
                }
            }

            $columnsToDrop = [
                'acct_status_type',
                'acct_unique_session_id',
                'acct_input_gigawords',
                'acct_output_gigawords',
                'acct_delay_time',
                'calling_station_id',
                'called_station_id',
                'connect_info',
                'terminate_cause_id',
                'raw_payload',
                'ingest_source',
                'received_at',
                'radius_nas_id',
                'nas_device_id',
            ];

            foreach ($columnsToDrop as $col) {
                if ($this->columnExists('radius_accounting', $col)) {
                    try {
                        $table->dropColumn($col);
                    } catch (\Throwable $e) {
                    }
                }
            }
        });
    }
};
