<?php

namespace App\Services\ISP\Accounting;

use App\Events\ISP\Accounting\AccountingIngestedEvent;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\RadiusNas;
use App\Models\ISP\NasDevice;
use App\Services\ISP\Session\OnlineSessionStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RadiusAccountingStateMachine
{
    public function __construct(
        private readonly OnlineSessionStore $onlineSessionStore,
    ) {}

    public const RFC_STATUS_START = 'start';
    public const RFC_STATUS_STOP = 'stop';
    public const RFC_STATUS_INTERIM = 'interim';
    public const RFC_STATUS_ON = 'accounting_on';
    public const RFC_STATUS_OFF = 'accounting_off';
    public const RFC_STATUS_FAILED = 'failed';

    public function ingest(array $normalized, string $ingestSource = 'free_radius_rest'): RadiusAccounting
    {
        return DB::transaction(function () use ($normalized, $ingestSource) {
            $acctSessionId = $normalized['acct_session_id']
                ?? throw new \InvalidArgumentException('acct_session_id wajib diisi');

            $statusType = $this->resolveStatusType($normalized['acct_status_type'] ?? null);

            $nas = $this->resolveNas(
                $normalized['nas_ip_address'] ?? null,
                $normalized['nas_name'] ?? null
            );
            $nasDevice = $this->resolveNasDevice($nas);

            $accounting = RadiusAccounting::firstOrNew([
                'acct_session_id' => $acctSessionId,
                'acct_status_type' => $statusType,
            ]);

            $isNew = !$accounting->exists;

            $octetsIn = (int)($normalized['acct_input_octets'] ?? 0);
            $octetsOut = (int)($normalized['acct_output_octets'] ?? 0);
            $gigawordsIn = (int)($normalized['acct_input_gigawords'] ?? 0);
            $gigawordsOut = (int)($normalized['acct_output_gigawords'] ?? 0);

            $cumulativeIn = $this->toBigIntOctets($octetsIn, $gigawordsIn);
            $cumulativeOut = $this->toBigIntOctets($octetsOut, $gigawordsOut);

            $start = $this->parseTimestamp($normalized['acct_start_time'] ?? null);
            $stop = null;
            if (in_array($statusType, [self::RFC_STATUS_STOP, self::RFC_STATUS_OFF, self::RFC_STATUS_FAILED], true)) {
                $stop = $this->parseTimestamp($normalized['acct_stop_time'] ?? null) ?? now();
            }

            if ($statusType === self::RFC_STATUS_START && $isNew) {
                $accounting->acct_start_time = $start ?? now();
            } elseif ($isNew) {
                $accounting->acct_start_time = $start ?? $accounting->acct_start_time;
            }

            $accounting->forceFill([
                'uuid' => $accounting->uuid ?? (string)Str::uuid(),
                'username' => $normalized['username'] ?? $accounting->username ?? null,
                'nas_ip_address' => $normalized['nas_ip_address'] ?? $accounting->nas_ip_address ?? null,
                'nas_port_id' => $normalized['nas_port_id'] ?? $accounting->nas_port_id ?? null,
                'framed_ip_address' => $normalized['framed_ip_address'] ?? $accounting->framed_ip_address ?? null,
                'framed_protocol' => $normalized['framed_protocol'] ?? $accounting->framed_protocol ?? null,
                'acct_unique_session_id' => $normalized['acct_unique_session_id'] ?? $accounting->acct_unique_session_id ?? null,
                'acct_input_octets' => max($accounting->acct_input_octets ?? 0, $cumulativeIn),
                'acct_output_octets' => max($accounting->acct_output_octets ?? 0, $cumulativeOut),
                'acct_input_gigawords' => $gigawordsIn,
                'acct_output_gigawords' => $gigawordsOut,
                'acct_input_packets' => (int)($normalized['acct_input_packets'] ?? $accounting->acct_input_packets ?? 0),
                'acct_output_packets' => (int)($normalized['acct_output_packets'] ?? $accounting->acct_output_packets ?? 0),
                'acct_session_time' => (int)($normalized['acct_session_time'] ?? $accounting->acct_session_time ?? 0),
                'acct_delay_time' => (int)($normalized['acct_delay_time'] ?? 0),
                'acct_terminate_cause' => $normalized['acct_terminate_cause'] ?? $accounting->acct_terminate_cause ?? null,
                'terminate_cause_id' => $normalized['terminate_cause_id'] ?? $accounting->terminate_cause_id ?? null,
                'calling_station_id' => $normalized['calling_station_id'] ?? $accounting->calling_station_id ?? null,
                'called_station_id' => $normalized['called_station_id'] ?? $accounting->called_station_id ?? null,
                'connect_info' => $normalized['connect_info'] ?? $accounting->connect_info ?? null,
                'acct_stop_time' => $stop ?? $accounting->acct_stop_time,
                'radius_nas_id' => $nas?->id ?? $accounting->radius_nas_id,
                'nas_device_id' => $nasDevice?->id ?? $accounting->nas_device_id,
                'raw_payload' => $normalized['raw_payload'] ?? $accounting->raw_payload ?? null,
                'ingest_source' => $ingestSource,
                'received_at' => now(),
            ]);

            if ($isNew) {
                $this->resolveLifecycleForeignKeys($accounting, $normalized);
            } else {
                $this->ensureLifecycleForeignKeys($accounting);
            }

            $accounting->save();

            try {
                event(new AccountingIngestedEvent($accounting, $statusType, $isNew));
            } catch (Throwable $e) {
                Log::warning('AccountingIngestedEvent dispatch failed', [
                    'acct_session_id' => $acctSessionId,
                    'err' => $e->getMessage(),
                ]);
            }

            try {
                $this->onlineSessionStore->upsertFromAccounting($accounting);
            } catch (Throwable $e) {
                Log::warning('OnlineSession accounting sync failed', [
                    'acct_session_id' => $acctSessionId,
                    'err' => $e->getMessage(),
                ]);
            }

            return $accounting;
        });
    }

    private function resolveStatusType(mixed $raw): string
    {
        if ($raw === null) {
            return self::RFC_STATUS_INTERIM;
        }

        $intMap = [
            1 => self::RFC_STATUS_START,
            2 => self::RFC_STATUS_STOP,
            3 => self::RFC_STATUS_INTERIM,
            7 => self::RFC_STATUS_ON,
            8 => self::RFC_STATUS_OFF,
        ];

        $strMap = [
            'start' => self::RFC_STATUS_START,
            'stop' => self::RFC_STATUS_STOP,
            'interim-update' => self::RFC_STATUS_INTERIM,
            'interim' => self::RFC_STATUS_INTERIM,
            'accounting-on' => self::RFC_STATUS_ON,
            'accounting_on' => self::RFC_STATUS_ON,
            'accounting-off' => self::RFC_STATUS_OFF,
            'accounting_off' => self::RFC_STATUS_OFF,
            'failed' => self::RFC_STATUS_FAILED,
        ];

        if (is_numeric($raw) && isset($intMap[(int)$raw])) {
            return $intMap[(int)$raw];
        }

        $lower = strtolower(trim((string)$raw));
        if (isset($strMap[$lower])) {
            return $strMap[$lower];
        }

        return self::RFC_STATUS_INTERIM;
    }

    private function resolveNas(?string $ip, ?string $name): ?RadiusNas
    {
        if (!$ip && !$name) {
            return null;
        }

        try {
            $query = RadiusNas::query();
            if ($ip) {
                $query->where('nas_ip_address', $ip);
            } elseif ($name) {
                $query->where('nas_name', $name);
            }
            return $query->first();
        } catch (Throwable $e) {
            Log::warning('Accounting resolveNas failed', ['ip' => $ip, 'err' => $e->getMessage()]);
            return null;
        }
    }

    private function resolveNasDevice(?RadiusNas $nas): ?NasDevice
    {
        if (!$nas) {
            return null;
        }

        try {
            if (method_exists($nas, 'nasDevice') && $nas->nasDevice) {
                return $nas->nasDevice;
            }
            return NasDevice::query()
                ->where('ip_address', $nas->nas_ip_address)
                ->active()
                ->first();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function resolveLifecycleForeignKeys(RadiusAccounting $accounting, array $normalized): void
    {
        if (empty($accounting->username)) {
            return;
        }

        $protocol = strtolower((string)($normalized['framed_protocol'] ?? $accounting->framed_protocol ?? ''));

        try {
            if ($protocol === 'ppp' || $protocol === '1') {
                $pppoe = PPPoEUser::query()->where('username', $accounting->username)->first();
                if ($pppoe) {
                    $accounting->pppoe_user_id = $pppoe->id;
                    $accounting->customer_service_id = $pppoe->customer_service_id;
                    return;
                }
            }
        } catch (Throwable $e) {
            Log::warning('resolveLifecycle PPPoE lookup failed', ['u' => $accounting->username, 'err' => $e->getMessage()]);
        }

        try {
            $hotspot = HotspotUser::query()->where('username', $accounting->username)->first();
            if ($hotspot) {
                $accounting->hotspot_user_id = $hotspot->id;
                $accounting->customer_service_id = $hotspot->customer_service_id ?? $accounting->customer_service_id;
            }
        } catch (Throwable $e) {
            Log::warning('resolveLifecycle Hotspot lookup failed', ['u' => $accounting->username, 'err' => $e->getMessage()]);
        }
    }

    private function ensureLifecycleForeignKeys(RadiusAccounting $accounting): void
    {
        if ($accounting->pppoe_user_id || $accounting->hotspot_user_id || $accounting->customer_service_id) {
            return;
        }
        if (empty($accounting->username)) {
            return;
        }

        $this->resolveLifecycleForeignKeys($accounting, [
            'framed_protocol' => $accounting->framed_protocol,
        ]);
    }

    private function toBigIntOctets(int $octets, int $gigawords): int
    {
        $giga = 4_294_967_296;
        return $octets + ($gigawords * $giga);
    }

    private function parseTimestamp(mixed $raw): ?\Illuminate\Support\Carbon
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        try {
            if (is_numeric($raw)) {
                return now()->setTimestamp((int)$raw);
            }
            return \Illuminate\Support\Carbon::parse($raw);
        } catch (Throwable $e) {
            return null;
        }
    }
}
