@extends('layouts.app')

@section('title', 'Kelola Budget')
@section('page-title', 'Kelola Budget')
@section('page-subtitle', 'Manajemen alokasi budget per kategori dan tahun.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Budget</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0">Daftar Budget Tahun {{ $year }}</h5>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form action="{{ route('budgets.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <label class="mb-0 text-nowrap fw-semibold small">Pilih Tahun:</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($i = date('Y') + 1; $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
                <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Budget
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="budgetsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Total Budget (Rp)</th>
                            <th>Terpakai (Rp)</th>
                            <th>Sisa (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgets as $index => $bud)
                        @php $sisa = $bud->budgets_total_budget - $bud->budgets_used_budget; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $bud->category->categories_name ?? '-' }}</td>
                            <td>{{ $bud->budgets_period_year }}</td>
                            <td>Rp {{ number_format($bud->budgets_total_budget, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($bud->budgets_used_budget, 0, ',', '.') }}</td>
                            <td class="{{ $sisa < 0 ? 'text-danger fw-bold' : 'text-success fw-semibold' }}">
                                Rp {{ number_format($sisa, 0, ',', '.') }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning btn-edit-budget"
                                    data-uuid="{{ $bud->budgets_uuid }}"
                                    data-category="{{ $bud->category->categories_name ?? '-' }}"
                                    data-total="{{ (int) $bud->budgets_total_budget }}"
                                    data-used="{{ (int) $bud->budgets_used_budget }}">
                                    <i class="bi bi-pencil-fill"></i> Ubah Limit
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
                <h5 class="modal-title" id="editModalTitle">Ubah Total Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_uuid">
                <div class="alert alert-warning small">
                    <strong>Budget terpakai saat ini:</strong> <span id="edit_used_label">Rp 0</span><br>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Total Budget Baru (Rp)</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control bg-white" id="edit_total" placeholder="Masukan Total Budget Baru (Rp)" required>
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
                <h5 class="modal-title">Tambah Alokasi Budget Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Tahun Periode</label>
                    <div class="col-sm-8">
                        <select class="form-select" id="create_year" required>
                            @for($i = date('Y') + 1; $i >= 2020; $i--)
                                <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Kategori</label>
                    <div class="col-sm-8">
                        <select class="form-select" id="create_category_uuid" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categories_uuid }}">{{ $cat->categories_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold">Total Budget (Rp)</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control bg-white" id="create_total" placeholder="Masukan Total Budget (Rp)" required>
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
    $('#budgetsTable').DataTable({
        responsive: true,
        language: {
            url: "{{ asset('vendor/datatables/id.json') }}",
            searchPlaceholder: "Cari budget..."
        }
    });

    $('#create_total, #edit_total').on('keyup', function() {
        $(this).val(formatRupiah($(this).val()));
    });

    $('#btnSaveCreate').on('click', function () {
        const year = $('#create_year').val();
        const catUuid = $('#create_category_uuid').val();
        const total = $('#create_total').val().replace(/\D/g, '');

        if (!catUuid || !total) {
            Swal.fire('Perhatian', 'Kategori dan Total Budget wajib diisi.', 'warning');
            return;
        }

        ajaxRequest({
            url: '{{ route("budgets.store") }}',
            data: {
                budgets_period_year: year,
                budgets_categories_uuid: catUuid,
                budgets_total_budget: total
            },
            confirmTitle: 'Simpan Alokasi Budget?',
            confirmMessage: 'Alokasi budget baru akan disimpan untuk tahun ' + year + '.',
            successCallback: function (response) {
                window.location.href = '{{ route("budgets.index") }}?year=' + response.year;
            }
        });
    });

    $(document).on('click', '.btn-edit-budget', function () {
        const uuid = $(this).data('uuid');
        const category = $(this).data('category');
        const total = Math.round(parseFloat($(this).data('total'))) || 0;
        const used = Math.round(parseFloat($(this).data('used'))) || 0;

        $('#edit_uuid').val(uuid);
        $('#edit_total').val(formatRupiah(String(total))).data('min-val', used);
        $('#edit_used_label').text('Rp ' + new Intl.NumberFormat('id-ID').format(used));

        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    $('#btnSaveEdit').on('click', function () {
        const uuid = $('#edit_uuid').val();
        const rawTotal = $('#edit_total').val().replace(/\D/g, '');
        const total = parseFloat(rawTotal) || 0;
        const min = parseFloat($('#edit_total').data('min-val')) || 0;

        if (!total) {
            Swal.fire('Perhatian', 'Total budget wajib diisi.', 'warning');
            return;
        }

        if (total < min) {
            Swal.fire('Validasi Gagal', 'Total budget tidak boleh lebih kecil dari budget yang sudah terpakai.', 'warning');
            return;
        }

        ajaxRequest({
            url: '/budgets/' + uuid,
            data: {
                budgets_total_budget: rawTotal
            },
            confirmTitle: 'Simpan Perubahan Budget?',
            confirmMessage: 'Jumlah limit budget akan diperbarui.',
            successCallback: function (response) {
                window.location.href = '{{ route("budgets.index") }}?year=' + response.year;
            }
        });
    });

});
</script>
@endpush
