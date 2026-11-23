<?php

namespace App\Models\Support;

use App\Traits\HasResellerScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasResellerScope;
    use HasFactory, \App\Traits\HasUserAreaScope, \App\Traits\HasBranchScope;

    protected $fillable = [
        'uuid',
        'customer_id',
        'title',
        'description',
        'resolution_notes',
        'category',
        'priority',
        'status',
        'assigned_to',
        'technician_ids',
        'reseller_id',
        'resolved_at',
        'resolved_by',
        'due_date',
        'photos',
        'optical_power',
        'digital_signature',
        'checklists',
        'requires_field_visit',
        'maintenance_wo_id',
        'installation_id',
        'sla_due_at',
        'sla_started_at',
        'sla_target_minutes',
        'sla_status',
        'sla_warning_sent_at',
        'sla_breached_sent_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'date',
        'photos' => 'array',
        'checklists' => 'array',
        'technician_ids' => 'array',
        'requires_field_visit' => 'boolean',
        'sla_due_at' => 'datetime',
        'sla_started_at' => 'datetime',
        'sla_target_minutes' => 'integer',
        'sla_warning_sent_at' => 'datetime',
        'sla_breached_sent_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Branch::class, 'branch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\Customer::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Dapatkan semua teknisi yang ditugaskan (Lead + Tim Partner)
     */
    public function assignedTechnicians(): \Illuminate\Database\Eloquent\Collection
    {
        $ids = is_array($this->technician_ids) ? $this->technician_ids : [];
        if (!empty($this->assigned_to) && !in_array($this->assigned_to, $ids)) {
            $ids[] = $this->assigned_to;
        }

        if (empty($ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return User::withoutGlobalScopes()->whereIn('id', $ids)->get();
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function maintenances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportMaintenance::class, 'ticket_id');
    }

    public function maintenanceWorkOrder(): BelongsTo
    {
        return $this->belongsTo(SupportMaintenance::class, 'maintenance_wo_id');
    }

    public function workOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Workforce\WorkOrder::class, 'ticket_id');
    }

    public function workOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Workforce\WorkOrder::class, 'ticket_id')->latestOfMany();
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\Installation::class, 'installation_id');
    }

    public function getTicketCodeAttribute(): string
    {
        return !empty($this->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $this->uuid), 0, 6)))
            : ('TKT-' . $this->id);
    }

    /**
     * Payload lengkap untuk notifikasi WhatsApp (Fonnte) & Telegram.
     * Mendukung: Objek Jaringan (ODP/Jalur/ODC), Calon Pelanggan (PSB), dan Pelanggan Resmi.
     */
    public function getNotificationPayload(array $overrides = []): array
    {
        $code = !empty($this->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $this->uuid), 0, 6)))
            : ('TKT-' . $this->id);

        $customer = (!empty($this->customer_id) && $this->relationLoaded('customer'))
            ? $this->customer
            : (!empty($this->customer_id) ? $this->customer()->first() : null);

        $assignedTo = (!empty($this->assigned_to) && $this->relationLoaded('assignedTo'))
            ? $this->assignedTo
            : (!empty($this->assigned_to) ? $this->assignedTo()->first() : null);

        $installation = (!empty($this->installation_id) && $this->relationLoaded('installation'))
            ? $this->installation
            : (!empty($this->installation_id) ? $this->installation()->with('survey')->first() : null);

        $isNetwork = false;
        $isPsb = in_array(strtolower((string) $this->category), ['installation', 'psb'], true);
        $objectName = null;
        $customerName = 'Pelanggan';
        $customerCode = null;
        $contactPhone = '-';
        $address = '-';
        $lat = null;
        $lng = null;
        $networkDetails = null;
        $deviceDetails = null;
        $cleanDesc = trim((string) $this->description);

        // 1. Parsing jika Objek Jaringan (manual_type = network atau customer_id kosong)
        if (empty($this->customer_id)) {
            if (preg_match('/\[Objek Jaringan:\s*(.+?)\]/i', $this->description, $m)) {
                $isNetwork = true;
                $objectName = trim($m[1]);
                $cleanDesc = trim(str_replace($m[0], '', $cleanDesc));
            } elseif (preg_match('/\[Calon Pelanggan:\s*(.+?)\s*\|\s*Telp:\s*(.+?)\s*\|\s*Alamat:\s*(.+?)\]/i', $this->description, $mPsb)) {
                $isPsb = true;
                $customerName = trim($mPsb[1]);
                $contactPhone = trim($mPsb[2]);
                $address = trim($mPsb[3]);
                $cleanDesc = trim(str_replace($mPsb[0], '', $cleanDesc));
            } else {
                // Customer ID kosong dan bukan PSB -> Dianggap Objek Jaringan / Gangguan Massal
                $isNetwork = true;
                $objectName = $this->title;
            }

            if ($isNetwork) {
                // Ekstrak Lokasi & PIC jika tersimpan di deskripsi
                if (preg_match('/Lokasi:\s*(.+?)(?=(?:\s+PIC:|\n|$))/i', $this->description, $mLoc)) {
                    $address = rtrim(trim($mLoc[1]), '.');
                    $cleanDesc = trim(preg_replace('/Lokasi:\s*.+?(?=(?:\s+PIC:|\n|$))/i', '', $cleanDesc));
                }
                if (preg_match('/PIC:\s*(.+?)(?=(?:\n|$))/i', $this->description, $mPic)) {
                    $contactPhone = rtrim(trim($mPic[1]), '.');
                    $cleanDesc = trim(preg_replace('/PIC:\s*.+?(?=(?:\n|$))/i', '', $cleanDesc));
                }

                // Cek jika nama objek mengandung nama/kode ODP yang terdaftar di database
                try {
                    $detectedOdp = null;
                    if (!empty($objectName)) {
                        $rawOdp = preg_replace('/\[|\]|ODP:|\(|\)/i', ' ', $objectName);
                        $words = array_filter(explode(' ', $rawOdp));
                        foreach ($words as $word) {
                            $word = trim($word);
                            if (strlen($word) >= 3) {
                                $detectedOdp = \App\Models\ISP\Odp::where('code', $word)
                                    ->orWhere('name', $word)
                                    ->first();
                                if ($detectedOdp) break;
                            }
                        }
                    }
                    if ($detectedOdp) {
                        $networkDetails = "ODP: {$detectedOdp->name}" . ($detectedOdp->code ? " ({$detectedOdp->code})" : '');
                        if ($detectedOdp->odc) {
                            $networkDetails .= " | ODC: {$detectedOdp->odc->name}";
                        }
                        if ($detectedOdp->olt) {
                            $networkDetails .= " | OLT: {$detectedOdp->olt->name}";
                        }
                        if ($address === '-' && !empty($detectedOdp->address)) {
                            $address = $detectedOdp->address;
                        }
                        if ($lat === null && !empty($detectedOdp->latitude)) {
                            $lat = $detectedOdp->latitude;
                            $lng = $detectedOdp->longitude;
                        }
                    }
                } catch (\Throwable) {
                    // Graceful fallback if database unbooted
                }
            }
        } elseif ($customer) {
            // 2. Pelanggan Resmi Terdaftar
            $customerName = $customer->name;
            $customerCode = $customer->code;
            $contactPhone = $customer->phone ?: '-';
            $address = $customer->address ?: '-';
            $lat = $customer->latitude;
            $lng = $customer->longitude;

            // Cari relasi layanan teknis pelanggan (ODP, ODC, Port, ONU, PPPoE)
            try {
                $service = $customer->customerServices()
                    ->with(['onu.odp.odc', 'onu.olt', 'pppoeUser', 'hotspotUser'])
                    ->first();

                if ($service) {
                    $onu = $service->onu;
                    $odp = $onu?->odp;
                    if (!$odp && $service->pppoeUser?->odp_id) {
                        $odp = \App\Models\ISP\Odp::find($service->pppoeUser->odp_id);
                    }
                    if (!$odp && $service->hotspotUser?->odp_id) {
                        $odp = \App\Models\ISP\Odp::find($service->hotspotUser->odp_id);
                    }

                    if ($odp) {
                        $port = $onu?->pon_port ?: ($service->pppoeUser?->port_number ?: ($service->hotspotUser?->port_number ?: null));
                        $networkDetails = "ODP: {$odp->name}" . ($odp->code ? " ({$odp->code})" : '') . ($port ? " (Port {$port})" : '');
                        if ($odp->odc) {
                            $networkDetails .= " | ODC: {$odp->odc->name}";
                        }
                        if ($odp->olt) {
                            $networkDetails .= " | OLT: {$odp->olt->name}";
                        }

                        // Fallback koordinat jika rumah pelanggan belum di-tag GPS tapi ODP ada
                        if (($lat === null || $lat == 0.0) && !empty($odp->latitude)) {
                            $lat = $odp->latitude;
                            $lng = $odp->longitude;
                        }
                    }

                    $dev = [];
                    if ($onu?->serial_number) {
                        $dev[] = "Modem: {$onu->serial_number}";
                    }
                    if ($service->pppoeUser?->username) {
                        $dev[] = "PPPoE: {$service->pppoeUser->username}";
                    } elseif ($service->hotspotUser?->username) {
                        $dev[] = "Hotspot: {$service->hotspotUser->username}";
                    }
                    if (!empty($dev)) {
                        $deviceDetails = implode(' | ', $dev);
                    }
                }
            } catch (\Throwable $se) {
                // Non-fatal fallback
            }
        } elseif ($installation) {
            // 3. SPK PSB Terintegrasi
            $isPsb = true;
            $customerName = $installation->customer_name ?: 'Calon Pelanggan';
            $contactPhone = $installation->customer_phone ?: '-';
            $address = $installation->installation_address ?: ($installation->survey?->address ?: '-');
            $lat = $installation->latitude ?: ($installation->survey?->latitude ?: null);
            $lng = $installation->longitude ?: ($installation->survey?->longitude ?: null);
        }

        // Clean up description trailing dots / whitespace
        $cleanDesc = trim(preg_replace('/\n{3,}/', "\n\n", $cleanDesc));

        $techCollection = $this->assignedTechnicians();
        if ($techCollection->isNotEmpty()) {
            $technicianName = $techCollection->pluck('name')->join(' & ');
        } else {
            $technicianName = $assignedTo?->name ?? 'Belum Ditugaskan';
        }

        $techniciansList = $techCollection->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'phone' => $t->phone ?? $t->whatsapp ?? null,
            ];
        })->values()->toArray();

        $payload = [
            'ticket_code'       => $code,
            'ticket_id'         => $this->id,
            'title'             => $this->title,
            'category'          => $this->category ?? 'internet',
            'priority'          => $this->priority ?? 'medium',
            'status'            => $this->status ?? 'open',
            'is_network_object' => $isNetwork,
            'is_psb'            => $isPsb,
            'object_name'       => $objectName,
            'customer_name'     => $customerName,
            'customer_code'     => $customerCode,
            'contact_phone'     => $contactPhone,
            'customer_address'  => $address,
            'address'           => $address,
            'latitude'          => $lat,
            'longitude'         => $lng,
            'network_details'   => $networkDetails,
            'device_details'    => $deviceDetails,
            'description'       => $cleanDesc,
            'technician_name'   => $technicianName,
            'technicians'       => $techniciansList,
        ];

        return array_merge($payload, $overrides);
    }
}
