<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #4F46E5;
            color: #fff;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            width: 50%;
            float: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>{{ $subtitle }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Service</th>
                <th>Paket</th>
                <th class="text-right">Harga [+PPN]</th>
                <th class="text-right">Fee Seller</th>
                <th class="text-right">Profit</th>
                <th>Tgl Aktif</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPpn = 0;
                $totalFee = 0;
                $totalProfit = 0;
            @endphp
            @foreach($rows as $index => $row)
                @php
                    $totalPpn += ($row['harga_ppn'] ?? 0);
                    $totalFee += ($row['fee_seller'] ?? 0);
                    $totalProfit += ($row['profit'] ?? 0);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['invoice_number'] ?? '-' }}</td>
                    <td>{{ $row['customer_name'] ?? '-' }}</td>
                    <td>{{ $row['service_type'] ?? '-' }}</td>
                    <td>{{ $row['package_name'] ?? '-' }}</td>
                    <td class="text-right">Rp{{ number_format($row['harga_ppn'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($row['fee_seller'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($row['profit'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $row['paid_at'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL</th>
                <th class="text-right">Rp{{ number_format($totalPpn, 0, ',', '.') }}</th>
                <th class="text-right">Rp{{ number_format($totalFee, 0, ',', '.') }}</th>
                <th class="text-right">Rp{{ number_format($totalProfit, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>