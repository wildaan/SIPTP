@extends('layouts.app')

@section('title', 'Kelola Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Manajemen daftar kategori pengajuan.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kategori</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Kategori</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $index => $cat)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $cat->categories_code }}</span></td>
                            <td>{{ $cat->categories_name }}</td>
                            <td>
                                @if($cat->categories_status == 1)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info btn-edit-cat"
                                    data-uuid="{{ $cat->categories_uuid }}"
                                    data-code="{{ $cat->categories_code }}"
                                    data-name="{{ $cat->categories_name }}">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                                <button class="btn btn-sm {{ $cat->categories_status == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-toggle-cat"
                                    data-uuid="{{ $cat->categories_uuid }}"
                                    data-status="{{ $cat->categories_status }}">
                                    <i class="bi {{ $cat->categories_status == 1 ? 'bi-toggle-off' : 'bi-toggle-on' }}"></i>
                                    {{ $cat->categories_status == 1 ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_uuid">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Kode Kategori</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="edit_code" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Nama Kategori</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="edit_name" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveEdit">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Kode Kategori</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="create_code" placeholder="Masukan Kode Kategori" required>
                        <div class="form-text">Kode harus unik.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Nama Kategori</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="create_name" placeholder="Masukan Nama Kategori" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveCreate">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#categoriesTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            searchPlaceholder: "Cari kategori..."
        }
    });

    // === CREATE ===
    $('#btnSaveCreate').on('click', function () {
        const code = $('#create_code').val().trim();
        const name = $('#create_name').val().trim();
        if (!code || !name) { Swal.fire('Perhatian', 'Semua field wajib diisi.', 'warning'); return; }

        ajaxRequest({
            url: '{{ route("categories.store") }}',
            data: { categories_code: code, categories_name: name },
            confirmTitle: 'Simpan Kategori Baru?',
            confirmMessage: 'Kategori "' + name + '" akan disimpan.',
            successCallback: function () { location.reload(); }
        });
    });

    // === EDIT — Open Modal ===
    $(document).on('click', '.btn-edit-cat', function () {
        $('#edit_uuid').val($(this).data('uuid'));
        $('#edit_code').val($(this).data('code'));
        $('#edit_name').val($(this).data('name'));
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    // === EDIT — Save ===
    $('#btnSaveEdit').on('click', function () {
        const uuid = $('#edit_uuid').val();
        const code = $('#edit_code').val().trim();
        const name = $('#edit_name').val().trim();
        if (!code || !name) { Swal.fire('Perhatian', 'Semua field wajib diisi.', 'warning'); return; }

        ajaxRequest({
            url: '/categories/' + uuid,
            data: { categories_code: code, categories_name: name },
            confirmTitle: 'Simpan Perubahan?',
            confirmMessage: 'Perubahan data kategori akan disimpan.',
            successCallback: function () { location.reload(); }
        });
    });

    // === TOGGLE STATUS ===
    $(document).on('click', '.btn-toggle-cat', function () {
        const uuid   = $(this).data('uuid');
        const status = $(this).data('status');
        const label  = status == 1 ? 'menonaktifkan' : 'mengaktifkan';

        ajaxRequest({
            url: '/categories/' + uuid + '/toggle-status',
            confirmTitle: 'Ubah Status?',
            confirmMessage: 'Anda akan ' + label + ' kategori ini.',
            successCallback: function () { location.reload(); }
        });
    });

});
</script>
@endpush
