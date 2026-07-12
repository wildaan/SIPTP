@extends('layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan')
@section('page-subtitle', 'Informasi lengkap pengajuan transaksi pengeluaran.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('submissions.index') }}">Pengajuan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('content')
<section class="section">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    {{-- Informasi Pengajuan --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Informasi Pengajuan
                            <span class="text-primary">({{ $submission->submissions_submissions_number }})</span>
                        </h5>
                    </div>
                    <div class="card-body col-md-10">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="30%">Tanggal Pengajuan</th>
                                <td>: {{ date('d F Y', strtotime($submission->submissions_date)) }}</td>
                            </tr>
                            <tr>
                                <th>Nama Pengaju</th>
                                <td>: {{ $submission->user->users_user_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>: <span class="badge bg-light text-dark border">{{ $submission->category->categories_name ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th>Nilai Pengajuan</th>
                                <td>: <strong class="text-primary">Rp {{ number_format($submission->submissions_amount, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <th>Status Saat Ini</th>
                                <td>:
                                    @php
                                        $statusMap = [
                                            3 => ['class' => 'secondary',    'text' => 'Waiting SPV Approval'],
                                            4 => ['class' => 'info',    'text' => 'Waiting Manager Approval'],
                                            5 => ['class' => 'warning',    'text' => 'Waiting Director Approval'],
                                            6 => ['class' => 'info',       'text' => 'Waiting Finance'],
                                            7 => ['class' => 'success',    'text' => 'Paid'],
                                            8 => ['class' => 'danger',     'text' => 'Rejected'],
                                        ];
                                        $st = $statusMap[$submission->submissions_status] ?? ['class' => 'secondary', 'text' => 'Unknown'];
                                    @endphp
                                    <span class="badge bg-{{ $st['class'] }}">{{ $st['text'] }}</span>
                                </td>
                            </tr>
                            @if($submission->submissions_status == 8 && $submission->submissions_reject_reason)
                            <tr>
                                <th>Alasan Penolakan</th>
                                <td class="text-danger fw-semibold">: {{ $submission->submissions_reject_reason }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Deskripsi</th>
                                <td>: {{ $submission->submissions_description }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                    {{-- Lampiran Dokumen --}}
                    <div class="card mt-3 col-md-8">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-paperclip me-1"></i> Lampiran Dokumen</h5>
                        </div>
                        <div class="card-body">
                            @if($submission->documents->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nama Pengaju</th>
                                                <th>Upload Time</th>
                                                <th>File</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submission->documents as $doc)
                                                <tr>
                                                    <td>{{ $doc->creator->users_user_name ?? '-' }}</td>
                                                    <td>{{ date('d M Y H:i:s', strtotime($doc->document_submission_create_date)) }}</td>
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>
                                                                <i class="bi bi-file-earmark me-1"></i>
                                                                {{ $doc->document_submission_file_name }}
                                                                <small class="text-muted">({{ number_format($doc->document_submission_file_size / 1024, 1) }} KB)</small>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" title="Lihat Dokumen" class="btn btn-sm btn-outline-primary btn-preview-doc"
                                                            data-path="{{ asset('storage/' . $doc->document_submission_file_path) }}"
                                                            data-name="{{ $doc->document_submission_file_name }}"
                                                            data-type="{{ $doc->document_submission_file_type }}">
                                                            <i class="bi bi-eye me-1"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="bi bi-x-circle me-1"></i> Tidak ada dokumen terlampir.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Action Panel --}}
                    @php
                        $roleCode   = Session::get('roles_code');
                        $status     = $submission->submissions_status;
                        $canApprove = false;
                        $canPay     = false;

                        if ($roleCode === 'spv' && $status == 3)      $canApprove = true;
                        if ($roleCode === 'manager' && $status == 4)   $canApprove = true;
                        if ($roleCode === 'direktur' && $status == 5)  $canApprove = true;
                        if ($roleCode === 'finance' && $status == 6)   $canPay = true;
                    @endphp

                    <div class="col-md-8">
                    @if($canApprove)
                    <div class="card border-start border-primary border-4 mt-3">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h5 class="card-title mb-0 text-primary">
                                <i class="bi bi-shield-check me-1"></i> Proses Approval
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Anda sebagai <strong>{{ strtoupper($roleCode) }}</strong>. Silakan proses pengajuan ini.</p>

                            <div class="row mb-3">
                                <label for="approval_notes" class="col-sm-3 col-form-label fw-semibold">Catatan (Opsional)</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control bg-white" id="approval_notes" rows="3" placeholder="Masukan Catatan"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-success btn-approve" data-action="approve">
                                    <i class="bi bi-check-circle-fill me-1"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-approve" data-action="reject">
                                    <i class="bi bi-x-circle-fill me-1"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($canPay)
                    <div class="card border-start border-info border-4 mt-3">
                        <div class="card-header bg-info bg-opacity-10">
                            <h5 class="card-title mb-0 text-info">
                                <i class="bi bi-wallet2 me-1"></i> Proses Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                $submissionAmount = $submission->submissions_amount;
                                $isBudgetSufficient = !is_null($budgetAvailable) && $budgetAvailable >= $submissionAmount;
                            @endphp

                            @if(!is_null($budgetAvailable) && $budgetAvailable >= $submissionAmount)
                                <div class="alert alert-success small mb-3">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <strong>Saldo Mencukupi</strong> — Sisa Budget Kategori: <strong>Rp {{ number_format($budgetAvailable, 0, ',', '.') }}</strong>
                                </div>
                            @else
                                <div class="alert alert-danger small mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>⚠ Saldo Tidak Mencukupi</strong> — Sisa Budget Kategori hanya <strong>Rp {{ number_format($budgetAvailable ?? 0, 0, ',', '.') }}</strong>, kurang <strong>Rp {{ number_format($submissionAmount - ($budgetAvailable ?? 0), 0, ',', '.') }}</strong> dari nilai pengajuan <strong>Rp {{ number_format($submissionAmount, 0, ',', '.') }}</strong>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <label for="payment_method" class="col-sm-3 col-form-label fw-semibold">Metode Pembayaran</label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="payment_method" @if(!$isBudgetSufficient) disabled @endif>
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="1">Transfer Bank</option>
                                        <option value="2">Kas / Tunai</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="payment_notes" class="col-sm-3 col-form-label fw-semibold">Catatan (Opsional)</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control bg-white" id="payment_notes" rows="2" placeholder="Masukan Catatan Pembayaran"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-success" id="btnPay" @if(!$isBudgetSufficient) disabled title="Saldo budget tidak mencukupi untuk memproses pembayaran ini" @endif>
                                    <i class="bi bi-cash-coin me-1"></i> Proses Pembayaran
                                </button>
                                <button type="button" class="btn btn-danger" id="btnRejectFinance">
                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!$canApprove && !$canPay)
                    <div class="card mt-3">
                        <div class="card-body text-center text-muted py-4">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            @if($status == 7)
                                <span class="text-success fw-semibold">Pengajuan ini sudah dibayar (Paid).</span>
                                <div class="col-md-12 mt-3">
                                    <a href="{{ route('submissions.exportPdf', $submission->submissions_uuid) }}" class="btn btn-info" target="_blank">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
                                    </a>
                                </div>
                            @elseif($status == 8)
                                <span class="text-danger fw-semibold">Pengajuan ini ditolak (Rejected)</span>
                            @else
                                Tidak ada aksi yang tersedia untuk Anda pada pengajuan ini.
                            @endif
                        </div>
                    </div>
                    @endif
                    </div>

                    <div class="col-md-4 mt-3">
                        <a href="{{ route('submissions.index') }}" class="btn btn-danger btn-md">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    {{--  Approval History  --}}
                    <div class="card mt-3 col-md-8">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-clock-history me-1"></i> Riwayat Approval</h5>
                        </div>
                        <div class="card-body">
                            @if($submission->approvals->count() > 0)
                                <div class="approval-timeline">
                                    @foreach($submission->approvals->sortBy('approvals_step') as $app)
                                    <div class="timeline-item d-flex mb-3">
                                        <div class="timeline-icon me-3 text-center" style="min-width: 40px;">
                                            @if($app->approvals_status == 1)
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                    <i class="bi bi-clock"></i>
                                                </div>
                                            @elseif($app->approvals_status == 2)
                                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                    <i class="bi bi-check-lg"></i>
                                                </div>
                                            @elseif($app->approvals_status == 3)
                                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                            @else
                                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                    <i class="bi bi-x-lg"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="timeline-content flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>Step {{ $app->approvals_step }} — {{ $app->role->roles_name ?? 'Unknown Role' }}</strong>
                                                    <span class="badge bg-{{ $app->approvals_status == 1 ? 'primary' : ($app->approvals_status == 2 ? 'success' : ($app->approvals_status == 3 ? 'warning' : 'danger')) }} ms-2">
                                                        @if($app->approvals_status == 1)
                                                              Created
                                                        @elseif($app->approvals_status == 2)
                                                            Approved
                                                        @elseif($app->approvals_status == 3)
                                                            Paid
                                                        @else
                                                            Rejected
                                                        @endif
                                                    </span>
                                                </div>
                                                <small class="text-muted">{{ date('d/m/Y H:i', strtotime($app->approvals_action_date)) }}</small>
                                            </div>

                                            <div class="text-muted small mt-1">
                                                Oleh: <strong>{{ $app->user->users_user_name ?? '-' }}</strong>
                                            </div>
                                            @if($app->approvals_notes)
                                                <div class="mt-1 p-2 bg-light rounded small">
                                                    <i class="bi bi-chat-left-text me-1"></i> {{ $app->approvals_notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-2 ms-5">
                                    @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Belum ada riwayat approval untuk pengajuan ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>



        </div>
    </div>
</section>

@endsection

@push('scripts')
<div class="modal fade" id="previewDocModal" tabindex="-1" aria-labelledby="previewDocModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewDocModalTitle">Preview Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0" id="previewDocModalBody" style="min-height: 400px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
            </div>
            <div class="modal-footer">
                <a href="#" id="btnDownloadDoc" class="btn btn-outline-secondary" download>
                    <i class="bi bi-download me-1"></i> Unduh File
                </a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    $('.btn-approve').on('click', function () {
        const action = $(this).data('action');
        const notes  = $('#approval_notes').val();
        const label  = action === 'approve' ? 'Proses' : 'Menolak';

        ajaxRequest({
            url: '{{ route("submissions.approve", $submission->submissions_uuid) }}',
            method: 'POST',
            data: {
                action: action,
                notes: notes
            },
            confirmTitle: label + ' Pengajuan ?',
            confirmMessage: 'Apakah anda yakin akan akan menyimpan data ini ?',
            successCallback: function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            }
        });
    });

    $('#btnPay').on('click', function () {
        const method = $('#payment_method').val();
        const notes  = $('#payment_notes').val();

        if (!method) {
            Swal.fire({
                icon: 'warning',
                title: 'Metode Pembayaran Belum Dipilih',
                text: 'Silakan pilih metode pembayaran terlebih dahulu sebelum memproses.',
                confirmButtonColor: '#435ebe'
            });
            return;
        }

        ajaxRequest({
            url: '{{ route("submissions.payment", $submission->submissions_uuid) }}',
            method: 'POST',
            data: {
                action: 'pay',
                payment_method: method,
                notes: notes
            },
            confirmTitle: 'Proses Pembayaran?',
            confirmMessage: 'Apakah anda yakin akan memproses pembayaran ini dengan total sebesar Rp {{ number_format($submission->submissions_amount, 0, ",", ".") }} ?',
            successCallback: function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            }
        });
    });

    $('#btnRejectFinance').on('click', function () {
        const notes = $('#payment_notes').val();

        ajaxRequest({
            url: '{{ route("submissions.payment", $submission->submissions_uuid) }}',
            method: 'POST',
            data: {
                action: 'reject',
                payment_method: 1,
                notes: notes || 'Ditolak oleh Finance: Saldo tidak mencukupi.'
            },
            confirmTitle: 'Tolak Pengajuan?',
            confirmMessage: 'Pengajuan ini akan ditolak karena saldo tidak mencukupi.',
            successCallback: function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            }
        });
    });

    $('.btn-preview-doc').on('click', function () {
        const path = $(this).data('path');
        const name = $(this).data('name');
        const type = $(this).data('type').toLowerCase();

        $('#previewDocModalTitle').text('Preview: ' + name);
        $('#btnDownloadDoc').attr('href', path);

        let content = '';

        if (type.includes('image') || type.includes('jpg') || type.includes('jpeg') || type.includes('png')) {
            content = `<img src="${path}" class="img-fluid p-2" style="max-height: 600px; object-fit: contain;">`;
        } else if (type.includes('pdf')) {
            content = `<iframe src="${path}" width="100%" height="600px" style="border: none;"></iframe>`;
        } else {
            content = `<div class="p-4 text-center">
                <i class="bi bi-file-earmark-arrow-down fs-1 d-block mb-2 text-warning"></i>
                Format file ini (${type}) tidak mendukung preview langsung.<br>
                Silakan unduh file untuk melihatnya.
            </div>`;
        }

        $('#previewDocModalBody').html(content);
        new bootstrap.Modal(document.getElementById('previewDocModal')).show();
    });

});
</script>
@endpush
