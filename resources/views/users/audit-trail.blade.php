@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')
@section('page-subtitle', 'Log aktivitas pengguna di dalam sistem.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
    <li class="breadcrumb-item active" aria-current="page">Audit Trail</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Log Aktivitas Pengguna</h4>
        </div>
        <div class="card-body">
            
            {{-- Filter Area --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <input type="text" id="search" class="form-control" placeholder="Cari aksi, deskripsi, ip address, atau username...">
                </div>
                <div class="col-md-2 mb-2 d-grid">
                    <button type="button" class="btn btn-secondary" onclick="if(window.dtAuditTrail) window.dtAuditTrail.draw();">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </div>

            {{-- Tabel Area --}}
            <div class="table-responsive">
                <table id="auditTrailTable" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>IP Address</th>
                            <th>Waktu Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
    window.dtAuditTrail = null;

    $(document).ready(function() {
        // Initialize DataTable
        window.dtAuditTrail = $('#auditTrailTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("audit-trail.list") }}',
                type: 'GET',
                data: function(d) {
                    d.search = { value: $('#search').val() }; // Map custom search to DT search
                }
            },
            columns: [
                { data: 'user_name', name: 'user_name' },
                { data: 'action', name: 'action' },
                { data: 'description', name: 'description' },
                { data: 'ip_address', name: 'ip_address' },
                { data: 'created_at', name: 'created_at' }
            ],
            language: {
                url: "{{ asset('vendor/datatables/id.json') }}",
                search: "",
                searchPlaceholder: "Cari data..."
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });

        // Search trigger on custom input
        $('#search').on('keyup', function() {
            window.dtAuditTrail.draw();
        });
    });
</script>
@endpush
