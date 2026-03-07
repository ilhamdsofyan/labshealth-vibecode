<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        body { margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background-color: #4F46E5; color: white; text-align: center; font-weight: bold; }
        td { text-align: center; }
        td:nth-child(2) { text-align: left; }
        tfoot td { font-weight: bold; background: #f0f0f0; }
        .footer { margin-top: 40px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h2>{{ $reportTitle }}</h2>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Nama Penyakit / Keluhan</th>
                <th width="60">SMA</th>
                <th width="60">GURU</th>
                <th width="80">KARYAWAN</th>
                <th width="60">UMUM</th>
                <th width="60">Total</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['data'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['disease_name'] }}</td>
                    <td>{{ $row['SMA'] }}</td>
                    <td>{{ $row['GURU'] }}</td>
                    <td>{{ $row['KARYAWAN'] }}</td>
                    <td>{{ $row['UMUM'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['notes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        @if($report['data']->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td>{{ $report['totals']['SMA'] }}</td>
                    <td>{{ $report['totals']['GURU'] }}</td>
                    <td>{{ $report['totals']['KARYAWAN'] }}</td>
                    <td>{{ $report['totals']['UMUM'] }}</td>
                    <td>{{ $report['totals']['total'] }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
