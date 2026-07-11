@extends('layouts.app')

@section('title', 'Buat Pengajuan')
@section('page-title', 'Pengajuan Baru')
@section('page-subtitle', 'Isi form untuk membuat pengajuan pengeluaran baru.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('submissions.index') }}">Pengajuan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
@endsection

@section('content')
<section class="section">
    <div class="row justify-content-left">
        <div class="col-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Pengajuan Transaksi Pengeluaran</h5>
                </div>
                <div class="card-body">
                    <form id="submissionForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="categories_uuid" class="col-sm-3 col-form-label fw-semibold">
                                Nomor Pengajuan
                            </label>
                            <div class="col-sm-9 d-flex align-items-center">
                                <strong>{{ $subNum }}</strong>
                                <input type="hidden" name="submission_number" value="{{ $subNum }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="categories_uuid" class="col-sm-3 col-form-label fw-semibold">
                                Tanggal Pengajuan <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="submissions_date" value="{{ $subNum }}" required >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="categories_uuid" class="col-sm-3 col-form-label fw-semibold">
                                Nama Pengaju
                            </label>
                            <div class="col-sm-9 d-flex align-items-center">
                                <strong>{{ Session::get('users_user_name') }}</strong>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="categories_uuid" class="col-sm-3 col-form-label fw-semibold">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <select class="form-select" id="categories_uuid" name="categories_uuid" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->categories_uuid }}">{{ $category->categories_name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-warning" id="budgetInfo"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="amount" class="col-sm-3 col-form-label fw-semibold">
                                Nilai Pengajuan (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <input type="text" id="amount" class="form-control" name="amount"
                                    placeholder="Masukan Nilai Pengajuan" required>
                                <div class="form-text text-muted" id="workflowHint"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-sm-3 col-form-label fw-semibold">
                                Deskripsi <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="description" name="description" rows="4"
                                    placeholder="Masukan Deskripsi / Keperluan" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-semibold">
                                Lampiran Dokumen <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="uploadTable">
                                        <thead>
                                            <tr>
                                                <th>Nama Pengaju</th>
                                                <th>Upload Time</th>
                                                <th>File <span class="text-danger">*</span></th>
                                                <th class="text-center" width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="upload-row">
                                                <td>
                                                    <input type="hidden" name="nama_pengaju[]" class="form-control"
                                                        value="{{ Session::get('users_user_name') }}"
                                                        placeholder="Masukan Nama Pengaju" required>
                                                        {{ Session::get('users_user_name') }}
                                                </td>
                                                <td>
                                                    <input type="hidden" name="upload_time[]" class="form-control upload-time-input"
                                                        value="{{ date('d M Y H:i:s') }}" readonly>
                                                        {{ date('d M Y H:i:s') }}
                                                </td>
                                                <td>
                                                    <input type="file" name="documents[]" class="form-control"
                                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete-row" disabled>
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" id="btnAddRow">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Dokumen
                                </button>
                                <div class="form-text text-muted mt-2">Format: PDF, JPG, JPEG, PNG. Maksimal 5 MB. Minimal harus ada 1 file yang diunggah.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('submissions.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="button" id="btnSubmit" class="btn btn-primary">
                                <i class="bi bi-send-fill me-1"></i> Submit Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // Rupiah input keyup formatting
    $('#amount').on('keyup', function() {
        $(this).val(formatRupiah($(this).val()));
        $(this).trigger('input'); // Refresh workflow hint
    });

    // Hint workflow berdasarkan nilai
    $('#amount').on('input', function () {
        const cleanVal = $(this).val().replace(/\D/g, '');
        const val = parseInt(cleanVal) || 0;
        let hint = '';
        if (val > 10000000) {
            hint = 'Notes : Untuk nilai lebih dari Rp 10jt Alur Approval : SPV → Manager → Direktur → Finance';
        } else if (val > 5000000) {
            hint = 'Notes : Untuk nilai lebih dari Rp 5jt Alur Approval : SPV → Manager → Finance';
        } else if (val > 0) {
            hint = 'Notes : Untuk nilai kurang dari Rp 5jt Alur Approval : SPV → Finance';
        }

        const selText = $('#categories_uuid option:selected').text().toLowerCase();
        if (selText.includes('po produk')) {
            hint = 'Notes : Khusus untuk kategori PO Produk setelah diajukan langsung ke Direktur lalu Finance';
        }
        $('#workflowHint').text(hint);
    });

    $('#categories_uuid').on('change', function () {
        // Trigger amount hint refresh
        $('#amount').trigger('input');
    });

    // Dynamic Upload Table Handlers
    function getFormattedTime() {
        const now = new Date();
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const day = String(now.getDate()).padStart(2, '0');
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
    }

    function checkRowButtons() {
        const rowCount = $('#uploadTable tbody tr').length;
        if (rowCount <= 1) {
            $('#uploadTable tbody tr .btn-delete-row').prop('disabled', true);
        } else {
            $('#uploadTable tbody tr .btn-delete-row').prop('disabled', false);
        }
    }

    $('#btnAddRow').on('click', function() {
        const timeStr = getFormattedTime();
        const username = "{{ Session::get('users_user_name') }}";
        const newRow = `
            <tr class="upload-row">
                <td>
                    <input type="hidden" name="nama_pengaju[]" class="form-control"
                        value="${username}" placeholder="Masukan Nama Pengaju">
                        ${username}
                </td>
                <td>
                    <input type="hidden" name="upload_time[]" class="form-control upload-time-input"
                        value="${timeStr}">
                        ${timeStr}
                </td>
                <td>
                    <input type="file" name="documents[]" class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-delete-row">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#uploadTable tbody').append(newRow);
        checkRowButtons();
    });

    $(document).on('click', '.btn-delete-row', function() {
        $(this).closest('tr').remove();
        checkRowButtons();
    });

    $(document).on('change', 'input[type="file"][name="documents[]"]', function() {
        const file = this.files[0];
        if (file) {
            const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const maxSize = 5 * 1024 * 1024; // 5 MB

            if (!allowedExtensions.includes(fileExtension)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Valid',
                    text: 'Format file harus berupa PDF, JPG, JPEG, atau PNG.',
                    confirmButtonColor: '#435ebe'
                });
                $(this).val('');
                return false;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Ukuran file maksimal adalah 5 MB.',
                    confirmButtonColor: '#435ebe'
                });
                $(this).val('');
                return false;
            }
        }
    });

    // Submit via AJAX (FormData untuk support file upload)
    $('#btnSubmit').on('click', function () {
        const form = document.getElementById('submissionForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let fileError = false;
        $('input[type="file"][name="documents[]"]').each(function() {
            const file = this.files[0];
            if (file) {
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                const maxSize = 5 * 1024 * 1024; // 5 MB

                if (!allowedExtensions.includes(fileExtension)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Tidak Valid',
                        text: 'Format file harus berupa PDF, JPG, JPEG, atau PNG.',
                        confirmButtonColor: '#435ebe'
                    });
                    $(this).val('');
                    fileError = true;
                    return false;
                }

                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran File Terlalu Besar',
                        text: 'Ukuran file maksimal adalah 5 MB.',
                        confirmButtonColor: '#435ebe'
                    });
                    $(this).val('');
                    fileError = true;
                    return false;
                }
            }
        });

        if (fileError) {
            return;
        }

        const formData = new FormData(form);

        ajaxFormSubmit({
            url: '{{ route("submissions.store") }}',
            formData: formData,
            confirmTitle: 'Submit Pengajuan?',
            confirmMessage: 'Apakah Anda yakin ingin mengirimkan pengajuan ini ?',
            loadingText: 'Mengunggah data dan dokumen...',
            successCallback: function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            }
        });
    });
});
</script>
@endpush
