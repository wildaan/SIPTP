@extends('layouts.app')

@section('title', 'Dashboard Utama')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data dan pengajuan terbaru.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Beranda</li>
@endsection

@section('content')
<section class="row">
    <div class="col-12 col-lg-9">
        {{-- Statistik Cards --}}
        <div class="row">
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon purple mb-2">
                                    <i class="bi bi-file-earmark-text-fill text-white"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Total Pengajuan</h6>
                                <h6 class="font-extrabold mb-0">{{ $stats['total'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon blue mb-2">
                                    <i class="bi bi-hourglass-split text-white"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Dalam Proses</h6>
                                <h6 class="font-extrabold mb-0">{{ $stats['process'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon green mb-2">
                                    <i class="bi bi-check-circle-fill text-white"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Disetujui</h6>
                                <h6 class="font-extrabold mb-0">{{ $stats['success'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon red mb-2">
                                    <i class="bi bi-x-circle-fill text-white"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Ditolak</h6>
                                <h6 class="font-extrabold mb-0">{{ $stats['reject'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Pengajuan Terbaru --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Pengajuan Terbaru</h4>
                        <a href="{{ route('submissions.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-lg">
                                <thead>
                                    <tr>
                                        <th>Nama Pengaju</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSubmissions as $sub)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <p class="font-bold mb-0">{{ $sub->user->users_user_name ?? '-' }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            {{ Str::limit($sub->submissions_description, 45) }}
                                            <br><small class="text-muted">{{ $sub->submissions_submissions_number }}</small>
                                        </td>
                                        <td>{{ date('d M Y', strtotime($sub->submissions_date)) }}</td>
                                        <td>
                                            @php
                                                $statusMap = [
                                                    1 => ['class' => 'secondary',  'text' => 'Draft'],
                                                    2 => ['class' => 'info',       'text' => 'Submitted'],
                                                    3 => ['class' => 'warning',    'text' => 'Waiting SPV'],
                                                    4 => ['class' => 'warning',    'text' => 'Waiting Manager'],
                                                    5 => ['class' => 'warning',    'text' => 'Waiting Director'],
                                                    6 => ['class' => 'info',       'text' => 'Waiting Finance'],
                                                    7 => ['class' => 'success',    'text' => 'Paid'],
                                                    8 => ['class' => 'danger',     'text' => 'Rejected'],
                                                ];
                                                $st = $statusMap[$sub->submissions_status] ?? ['class' => 'secondary', 'text' => 'Draft'];
                                            @endphp
                                            <span class="badge bg-{{ $st['class'] }}">{{ $st['text'] }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('submissions.show', $sub->submissions_uuid) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada pengajuan terbaru.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Samping Kanan --}}
    <div class="col-12 col-lg-3">
        {{-- Profil User --}}
        <div class="card">
            <div class="card-body py-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xl bg-primary d-flex align-items-center justify-content-center rounded-circle text-white fw-bold" style="width:50px; height:50px; font-size:1.2rem;">
                        {{ strtoupper(substr(Session::get('users_user_name', 'U'), 0, 2)) }}
                    </div>
                    <div class="ms-3 name">
                        <h5 class="font-bold mb-0">{{ Session::get('users_user_name') }}</h5>
                        <h6 class="text-muted mb-0">{{ Session::get('roles_name') }}</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aktivitas Terakhir --}}
        <div class="card">
            <div class="card-header">
                <h4>Aktivitas Terakhir</h4>
            </div>
            <div class="card-content pb-4">
                @forelse($recentActivity as $act)
                <div class="recent-message d-flex px-4 py-3 border-bottom">
                    <div class="name">
                        <h6 class="mb-1 text-sm">
                            {{ $act->user_activity_action }}
                        </h6>
                        <small class="text-muted mb-0 d-block">
                            {{ $act->user_activity_description }}
                        </small>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            {{ \Carbon\Carbon::parse($act->user_activity_create_date)->diffForHumans() }}
                        </small>
                    </div>
                </div>
                @empty
                <div class="px-4 py-3 text-muted text-center small">Belum ada aktivitas persetujuan.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
