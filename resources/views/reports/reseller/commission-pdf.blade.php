<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Komisi - {{ $monthName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #1e3a8a;
        }
        .header p {
            margin: 0;
            color: #64748b;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
            width: 25%;
            background-color: #f8fafc;
        }
        .summary-box .label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-box .value {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }
        .summary-box .value.profit {
            color: #059669;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            font-size: 10px;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN KOMISI & MARGIN RESELLER</h1>
        <p>Reseller: {{ $reseller->name }} | Periode: {{ $monthName }}</p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="label">Total Penjualan</div>
                <div class="value">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Harga Modal (Pusat)</div>
                <div class="value">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Estimasi Laba Bersih</div>
                <div class="value profit">Rp {{ number_format($netMargin, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Rasio Margin</div>
                <div class="value">{{ number_format($profitMarginPercent, 1, ',', '.') }}%</div>
            </td>
        </tr>
    </table>

    <h3 style="font-size: 13px; margin-bottom: 10px; color: #334155;">Rincian Transaksi Komisi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Tgl Transaksi</th>
                <th width="30%">Pelanggan / No Ref</th>
                <th width="20%">Paket / Layanan</th>
                <th width="15%" class="text-right">Harga Jual</th>
                <th width="10%" class="text-right">Modal</th>
                <th width="10%" class="text-right">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $row)
                <tr>
                    <td>{{ $row['paid_at'] }}</td>
                    <td>
                        <strong>{{ $row['customer_name'] ?? '-' }}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">Ref: {{ $row['invoice_number'] }}</span>
                    </td>
                    <td>
                        {{ $row['package_name'] ?? '-' }}<br>
                        <span style="font-size: 9px; color: #64748b;">{{ $row['service_type'] }}</span>
                    </td>
                    <td class="text-right">Rp {{ number_format($row['harga_ppn'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #059669; font-weight: bold;">+ Rp {{ number_format($row['fee_seller'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data transaksi komisi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} oleh {{ $reseller->name }} melalui sistem dsBilling.
    </div>

</body>
</html>