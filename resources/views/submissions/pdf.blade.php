<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Pengajuan {{ $submission->submissions_submissions_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #25396f;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #25396f;
            font-size: 20px;
        }
        .header p {
            margin: 4px 0 0;
            color: #666;
            font-size: 11px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #25396f;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table th, .info-table td {
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }
        .info-table th {
            width: 25%;
            font-weight: bold;
            color: #555;
        }
        .info-table td {
            color: #111;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f7ff;
            color: #25396f;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            color: #0f5132;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p>Sistem Informasi Pengajuan Transaksi Pengeluaran (SIPTP)</p>
    </div>

    <table class="info-table">
        <tr>
            <th>No. Pengajuan</th>
            <td>: <strong style="color: #25396f; font-size: 12px;">{{ $submission->submissions_submissions_number }}</strong></td>
        </tr>
        <tr>
            <th>Tanggal Pengajuan</th>
            <td>: {{ date('d F Y', strtotime($submission->submissions_date)) }}</td>
        </tr>
        <tr>
            <th>Nama Pengaju</th>
            <td>: {{ $submission->user->users_user_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Kategori</th>
            <td>: {{ $submission->category->categories_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nilai Pengajuan</th>
            <td>: <strong style="color: #25396f;">Rp {{ number_format($submission->submissions_amount, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <th>Deskripsi / Keperluan</th>
            <td>: {{ $submission->submissions_description }}</td>
        </tr>
        <tr>
            <th>Status Pengajuan</th>
            <td>: <span class="badge">PAID</span></td>
        </tr>
        @if($submission->payment)
        <tr>
            <th>Metode Pembayaran</th>
            <td>: {{ $submission->payment->payments_method == 1 ? 'Transfer Bank' : 'Kas / Tunai' }}</td>
        </tr>
        <tr>
            <th>Tanggal Bayar</th>
            <td>: {{ date('d F Y H:i', strtotime($submission->payment->payments_date)) }}</td>
        </tr>
        <tr>
            <th>Catatan Pembayaran</th>
            <td>: {{ $submission->payment->payments_notes ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">Dokumen Lampiran</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30%">Nama Pengaju</th>
                <th width="30%">Upload Time</th>
                <th>Nama File</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submission->documents as $doc)
            <tr>
                <td>{{ $doc->creator->users_user_name ?? '-' }}</td>
                <td>{{ date('d M Y H:i:s', strtotime($doc->document_submission_create_date)) }}</td>
                <td>{{ $doc->document_submission_file_name }} ({{ number_format($doc->document_submission_file_size / 1024, 1) }} KB)</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #888;">Tidak ada dokumen lampiran</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Riwayat Approval</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Step</th>
                <th width="25%">Role</th>
                <th width="25%">Nama Pemeriksa</th>
                <th width="20%">Tanggal Aksi</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submission->approvals->sortBy('approvals_step') as $app)
            <tr>
                <td style="text-align: center;">{{ $app->approvals_step }}</td>
                <td>{{ $app->role->roles_name ?? '-' }}</td>
                <td>{{ $app->user->users_user_name ?? '-' }}</td>
                <td>{{ date('d/m/Y H:i', strtotime($app->approvals_action_date)) }}</td>
                <td>{{ $app->approvals_notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #888;">Belum ada riwayat approval</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
         Sistem Informasi Pengajuan Transaksi Pengeluaran (SIPTP) {{ date('d F Y H:i:s') }}.
    </div>
</body>
</html>
