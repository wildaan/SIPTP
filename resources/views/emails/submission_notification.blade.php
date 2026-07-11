<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        .email-header {
            background-color: #435ebe;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-body {
            padding: 30px 20px;
        }
        .email-footer {
            background-color: #f1f3f5;
            padding: 15px 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .submission-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.info-table td {
            padding: 5px 0;
        }
        table.info-table td:first-child {
            font-weight: bold;
            width: 40%;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2 style="margin: 0;">SIPTP Notification</h2>
        </div>
        
        <div class="email-body">
            <p><strong>{{ $greeting }}</strong></p>
            
            @foreach($lines as $line)
                <p>{{ $line }}</p>
            @endforeach
            
            <div class="submission-info">
                <table class="info-table">
                    <tr>
                        <td>Nomor Pengajuan</td>
                        <td>: {{ $submission->submissions_submissions_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ date('d/m/Y', strtotime($submission->submissions_date)) }}</td>
                    </tr>
                    <tr>
                        <td>Nilai</td>
                        <td>: Rp {{ number_format($submission->submissions_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($submission->submissions_description)
                    <tr>
                        <td>Deskripsi</td>
                        <td>: {{ $submission->submissions_description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <p>Terima kasih,<br>Sistem Informasi Pengajuan Transaksi Pengeluaran (SIPTP)</p>
        </div>
        
        <div class="email-footer">
            Email ini dihasilkan secara otomatis oleh sistem SIPTP. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
