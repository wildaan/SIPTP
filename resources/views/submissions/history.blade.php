@extends('layouts.app')

@section('title', $role === 'finance' ? 'Riwayat Pembayaran' : 'Riwayat Approval')
@section('page-title', $role === 'finance' ? 'Riwayat Pembayaran' : 'Riwayat Approval')
@section('page-subtitle', $role === 'finance' ? 'Daftar transaksi yang telah diproses pembayarannya atau ditolak.' : 'Daftar pengajuan yang telah Anda setujui atau tolak.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $role === 'finance' ? 'Riwayat Pembayaran' : 'Riwayat Approval' }}</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Data Riwayat</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="historyTable">
                    <thead>
                        <tr>
                            <th>No. Pengajuan</th>
                            <th>Tanggal</th>
                            <th>Pengaju</th>
                            <th>Kategori</th>
                            <th>Nilai (Rp)</th>
                            <th>Status Terkini</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $sub)
                        <tr>
                            <td><strong class="text-primary">{{ $sub->submissions_submissions_number }}</strong></td>
                            <td>{{ date('d M Y', strtotime($sub->submissions_date)) }}</td>
                            <td>{{ $sub->user->users_user_name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $sub->category->categories_name ?? '-' }}</span></td>
                            <td>Rp {{ number_format($sub->submissions_amount, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        3 => ['class' => 'secondary',    'text' => 'Waiting SPV Approval'],
                                        4 => ['class' => 'info',    'text' => 'Waiting Manager Approval'],
                                        5 => ['class' => 'warning',    'text' => 'Waiting Director Approval'],
                                        6 => ['class' => 'info',       'text' => 'Waiting Finance'],
                                        7 => ['class' => 'success',    'text' => 'Paid'],
                                        8 => ['class' => 'danger',     'text' => 'Rejected'],
                                    ];
                                    $st = $statusMap[$sub->submissions_status] ?? ['class' => 'secondary', 'text' => 'Draft'];
                                @endphp
                                <span class="badge bg-{{ $st['class'] }}">{{ $st['text'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('submissions.show', $sub->submissions_uuid) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye-fill"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#historyTable').DataTable({
        responsive: true,
        order: [[1, 'desc']], // Sort by date descending
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            searchPlaceholder: "Cari riwayat..."
        }
    });
});
</script>
@endpush
