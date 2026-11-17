<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $dispatch->dispatch_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page-container {
            max-width: 820px;
            margin: 20px auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .no-print-bar {
            max-width: 820px;
            margin: 20px auto 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 4px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: none;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #f8fafc;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .company-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .company-info p {
            color: #475569;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .doc-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .meta-col h3 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }
        .meta-table {
            width: 100%;
            font-size: 11px;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 110px;
            color: #64748b;
        }
        .meta-table td.colon {
            width: 12px;
            color: #64748b;
        }
        .meta-table td.val {
            font-weight: 600;
            color: #0f172a;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-items th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 8px 10px;
            text-align: left;
        }
        .table-items th.center, .table-items td.center {
            text-align: center;
        }
        .table-items th.right, .table-items td.right {
            text-align: right;
        }
        .table-items td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }
        .table-items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .sn-list {
            margin-top: 4px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .sn-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            color: #1e293b;
        }
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 11px;
            color: #92400e;
            margin-bottom: 24px;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #ffffff;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sig-title {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
        .sig-line {
            margin-top: 50px;
            border-top: 1px solid #0f172a;
            padding-top: 4px;
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 10px;
            color: #64748b;
        }
        .footer-note {
            margin-top: 24px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print, .no-print-bar {
                display: none !important;
            }
            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 15px 20px !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .table-items th {
                background-color: #0f172a !important;
                color: #ffffff !important;
            }
            .meta-grid {
                background: #f8fafc !important;
            }
            .signatures {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- Action Bar (Hidden on Print) --}}
    <div class="no-print-bar no-print">
        <a href="{{ route('inventory.dispatches.index') }}" class="btn btn-secondary">
            &larr; Kembali ke Daftar Barang Keluar
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak / Download PDF
        </button>
    </div>

    <div class="page-container">
        {{-- Header ISP / Perusahaan --}}
        <div class="header">
            <div class="company-info">
                @if(!empty($company['logo_url']))
                    <img src="{{ $company['logo_url'] }}" alt="Logo" style="height: 38px; margin-bottom: 6px; object-fit: contain;">
                @endif
                <h1>{{ $company['name'] ?? config('app.name', 'dsBilling ISP Network') }}</h1>
                <p>{{ $company['address'] ?? 'Internet Service Provider & Telecommunication Services' }}</p>
                <p>Telp: {{ $company['phone'] ?? $company['mobile'] ?? '-' }} | Email: {{ $company['email'] ?? '-' }}</p>
            </div>
            <div class="doc-title">
                <h2>SURAT JALAN</h2>
                <div class="doc-badge">{{ $dispatch->dispatch_number }}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                    Tanggal: <strong>{{ $dispatch->dispatched_at ? $dispatch->dispatched_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        </div>

        {{-- Meta Info Grid --}}
        <div class="meta-grid">
            {{-- Asal Pengiriman --}}
            <div class="meta-col">
                <h3>ASAL PENGELUARAN</h3>
                <table class="meta-table">
                    <tr>
                        <td class="label">Gudang Asal</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->warehouse->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cabang / Wilayah</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->branch->name ?? 'Pusat' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Petugas Gudang</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->dispatcher->name ?? auth()->user()->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Metode Distribusi</td>
                        <td class="colon">:</td>
                        <td class="val">{{ strtoupper(str_replace('_', ' ', $dispatch->distribution_method ?: 'REGULAR')) }}</td>
                    </tr>
                </table>
            </div>

            {{-- Tujuan & Penerima --}}
            <div class="meta-col">
                <h3>TUJUAN / PENERIMA</h3>
                <table class="meta-table">
                    <tr>
                        <td class="label">Penerima (Staf/Mitra)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->recipient->name ?? '-' }} ({{ strtoupper($dispatch->recipient_type ?: ($dispatch->recipient->role ?? 'TEKNISI')) }})</td>
                    </tr>
                    <tr>
                        <td class="label">Peruntukan / Destinasi</td>
                        <td class="colon">:</td>
                        <td class="val">
                            @if($dispatch->customer)
                                <span style="color: #2563eb;">{{ $dispatch->customer->name }}</span> ({{ $dispatch->customer->customer_id ?? 'Pelanggan' }})
                            @elseif($dispatch->installation)
                                PSB: {{ $dispatch->installation->installation_number ?? '-' }}
                            @elseif($dispatch->ticket)
                                Tiket Gangguan: #{{ $dispatch->ticket->ticket_number ?? '-' }}
                            @else
                                Buffer Stok / Operasional Lapangan
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">No. Permintaan / Ref</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->requisition->requisition_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat / Lokasi</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $dispatch->customer->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Tabel Daftar Barang --}}
        <table class="table-items">
            <thead>
                <tr>
                    <th class="center" style="width: 32px;">No</th>
                    <th style="width: 100px;">Kode / SKU</th>
                    <th>Nama Barang & Rincian Perangkat</th>
                    <th class="center" style="width: 70px;">Qty</th>
                    <th class="center" style="width: 60px;">Satuan</th>
                    <th style="width: 140px;">Keterangan / Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @forelse($dispatch->items as $idx => $di)
                    @php
                        $qty = (float) $di->quantity;
                        $totalQty += $qty;
                        $serials = is_array($di->serials) ? $di->serials : json_decode($di->serials ?? '[]', true);
                    @endphp
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="font-mono" style="font-weight: 600; color: #475569;">{{ $di->item->sku ?? '-' }}</td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 12px;">{{ $di->item->name ?? '-' }}</div>
                            @if($di->item->brand || $di->item->model)
                                <div style="font-size: 10px; color: #64748b;">
                                    {{ $di->item->brand }} {{ $di->item->model ? '• ' . $di->item->model : '' }}
                                </div>
                            @endif

                            {{-- Tampilkan Daftar Serial Number jika ada --}}
                            @if(!empty($serials))
                                <div class="sn-list">
                                    @foreach($serials as $sn)
                                        <span class="sn-chip">SN: {{ $sn }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="center" style="font-weight: 700; font-size: 12px;">{{ $qty }}</td>
                        <td class="center" style="color: #64748b;">{{ $di->item->unit ?? 'Unit' }}</td>
                        <td style="font-size: 10px; color: #475569;">{{ $di->notes ?: ($di->item->is_serialized ? 'Serial Unit Terverifikasi' : 'Barang Non-Serial') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center" style="padding: 20px; color: #94a3b8;">Tidak ada data item dalam surat jalan ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: 700;">
                    <td colspan="3" class="right" style="padding: 8px 10px;">TOTAL UNIT BARANG KELUAR:</td>
                    <td class="center" style="font-size: 13px; color: #2563eb;">{{ $totalQty }}</td>
                    <td class="center">Item</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Catatan Tambahan --}}
        @if(!empty($dispatch->notes))
            <div class="notes-box">
                <strong>Catatan / Pesan Pengiriman:</strong>
                <div>{{ $dispatch->notes }}</div>
            </div>
        @endif

        {{-- Syarat & Ketentuan Singkat Surat Jalan --}}
        <div style="font-size: 10px; color: #64748b; margin-bottom: 16px; border-left: 2px solid #cbd5e1; padding-left: 8px;">
            <em>Harap periksa kelengkapan fisik, kondisi segel, dan Serial Number (SN) barang sebelum menandatangani bukti serah terima ini. Barang yang sudah diterima menjadi tanggung jawab pihak penerima sesuai peruntukan operasional.</em>
        </div>

        {{-- Kolom Tanda Tangan (4 Pihak) --}}
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-title">Diserahkan Oleh (Gudang)</div>
                <div>
                    <div class="sig-line">{{ $dispatch->dispatcher->name ?? auth()->user()->name ?? '(.......................)' }}</div>
                    <div class="sig-sub">Petugas Logistik</div>
                </div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Pembawa / Kurir</div>
                <div>
                    <div class="sig-line">(...................................)</div>
                    <div class="sig-sub">Kurir / Driver / Staf</div>
                </div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Diterima Oleh</div>
                <div>
                    <div class="sig-line">{{ $dispatch->recipient->name ?? '(.......................)' }}</div>
                    <div class="sig-sub">{{ strtoupper($dispatch->recipient_type ?: ($dispatch->recipient->role ?? 'Penerima')) }}</div>
                </div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Mengetahui / Supervisor</div>
                <div>
                    <div class="sig-line">(...................................)</div>
                    <div class="sig-sub">Head of Tech / Operational</div>
                </div>
            </div>
        </div>

        <div class="footer-note">
            Dokumen resmi diterbitkan secara otomatis oleh Sistem dsBilling ISP Inventory. Cetak pada: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

</body>
</html>
