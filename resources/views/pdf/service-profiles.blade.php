<!DOCTYPE html>
<html>
<head>
    <title>Daftar Paket Internet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Daftar Paket Internet</h1>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Paket</th>
                <th>Nama Paket</th>
                <th>Jenis Layanan</th>
                <th>Download/Upload</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profiles as $index => $profile)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $profile->code }}</td>
                    <td>{{ $profile->name }}</td>
                    <td>{{ strtoupper($profile->service_type) }}</td>
                    <td>{{ $profile->download_speed }} / {{ $profile->upload_speed }} Mbps</td>
                    <td>{{ $profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-' }}</td>
                    <td>{{ $profile->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>