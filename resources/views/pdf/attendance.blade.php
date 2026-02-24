<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekod Kehadiran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background-color: #1e5c7b;
            color: #fff;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            text-align: left;
        }
        td { padding: 8px 12px; font-size: 11px; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #ddeef6; }
        tr:nth-child(odd) { background-color: #fff; }
        .no-col { width: 5%; text-align: center; }
    </style>
</head>
<body>
    <h2>Rekod Kehadiran — {{ $meetingTitle }}</h2>
    <p class="subtitle">Kategori: {{ $categoryLabel }} | Tarikh: {{ $meetingDate }}</p>

    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th>Nama</th>
                <th>No. Kad Pengenalan</th>
                <th>Jawatan</th>
                @if ($isMpkk)
                    <th>MPKK</th>
                @endif
                <th>No Telefon</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
                <tr>
                    <td class="no-col">{{ $i + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['ic_number'] }}</td>
                    <td>{{ $row['position_type'] ?? '-' }}</td>
                    @if ($isMpkk)
                        <td>{{ $row['position_name'] ?? '-' }}</td>
                    @endif
                    <td>{{ $row['phone_number'] ?? '-' }}</td>
                    <td>{{ $row['address'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $isMpkk ? 7 : 6 }}" style="text-align:center;">Tiada rekod.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
