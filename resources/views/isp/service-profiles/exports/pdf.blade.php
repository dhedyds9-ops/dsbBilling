<!DOCTYPE html>
<html>
<head>
    <title>Service Profiles</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Service Profiles</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Download</th>
                <th>Upload</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profiles as $profile)
            <tr>
                <td>{{ $profile->id }}</td>
                <td>{{ $profile->code }}</td>
                <td>{{ $profile->name }}</td>
                <td>{{ $profile->serviceProfileType?->name }}</td>
                <td>{{ $profile->download_speed ?: '-' }} Mbps</td>
                <td>{{ $profile->upload_speed ?: '-' }} Mbps</td>
                <td>{{ $profile->price ? 'Rp ' . number_format($profile->price, 0, ',', '.') : '-' }}</td>
                <td>{{ $profile->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>