@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')
@section('page-subtitle', 'Kelola data pengguna, hak akses, dan status akun.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
    <li class="breadcrumb-item active" aria-current="page">Kelola Pengguna</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Daftar Pengguna</h4>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="bi bi-person-plus-fill me-2"></i> Tambah User
            </button>
        </div>
        <div class="card-body">
            
            {{-- Filter Area --}}
            <div class="row mb-4">
                <div class="col-md-4 mb-2">
                    <input type="text" id="search" class="form-control" placeholder="Cari nama, username, email...">
                </div>
                <div class="col-md-3 mb-2">
                    <select id="filterRole" class="form-select">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->roles_uuid }}">{{ $role->roles_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="2">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            {{-- Tabel Area --}}
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
        </div>
    </div>
</section>

{{-- Modal Form User (Create / Edit) --}}
<div class="modal fade" id="modalUserForm" tabindex="-1" aria-labelledby="modalUserFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formUser" onsubmit="submitUserForm(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUserFormTitle">Form User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    
                    <div class="row mb-3">
                        <label for="users_user_name" class="col-sm-4 col-form-label">Username <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control bg-white" id="users_user_name" name="users_user_name" placeholder="Masukan Username" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="users_email" class="col-sm-4 col-form-label">Email <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control bg-white" id="users_email" name="users_email" placeholder="Masukan Email" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Role <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="hidden" id="current_role_uuid">

                            <div id="roleDisplayWrapper" style="display:flex; align-items:center; gap:8px;">
                                <span id="roleDisplayText" class="fw-semibold"></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="enableRoleChange()">
                                    <i class="bi bi-pencil-square"></i> Ubah Role
                                </button><br/>
                            </div>

                            <div id="roleSelectWrapper" style="display:none;">
                                <select class="form-select bg-white" id="roles_uuid" name="roles_uuid" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->roles_uuid }}">{{ $role->roles_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Hak Akses</label>
                        <div class="col-sm-8">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="users_is_admin" name="users_is_admin" value="1">
                                <label class="form-check-label" for="users_is_admin">
                                    Jadikan sebagai Admin 
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="row mb-3">
                        <label for="password" class="col-sm-4 col-form-label">Password <span class="text-danger id-pass-req">*</span></label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control bg-white" id="password" name="password" placeholder="Masukan Password">
                            <div class="form-text id-pass-help" style="display:none;">Kosongkan jika tidak ingin mengubah password.</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="password_confirmation" class="col-sm-4 col-form-label">Konfirmasi Password <span class="text-danger id-pass-req">*</span></label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control bg-white" id="password_confirmation" name="password_confirmation" placeholder="Masukan Konfirmasi Password">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary ml-1">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script>
    let userModal = null;
    let resetModal = null;
    window.dtUsers = null;

    $(document).ready(function() {
        // Initialize Modals
        userModal = new bootstrap.Modal(document.getElementById('modalUserForm'));
        // Initialize DataTable
        window.dtUsers = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ url("/users/list") }}',
                type: 'GET',
                data: function(d) {
                    d.search = { value: $('#search').val() }; // Map custom search to DT search
                    d.role = $('#filterRole').val();
                    d.status = $('#filterStatus').val();
                }
            },
            columns: [
                { data: 'users_user_name', name: 'users_user_name' },
                { data: 'users_email', name: 'users_email' },
                { data: 'role', name: 'role', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
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
            window.dtUsers.draw();
        });
        
        // Filter change trigger
        $('#filterRole, #filterStatus').on('change', function() {
            window.dtUsers.draw();
        });
    });

    function enableRoleChange() {
        $('#roleDisplayWrapper').hide();
        $('#roleSelectWrapper').show();
    }
    // OPEN CREATE MODAL
    function openCreateModal() {
        $('#formUser')[0].reset();
        $('#user_id').val('');
        $('#current_role_uuid').val('');
        $('#modalUserFormTitle').text('Tambah User Baru');
        
        $('#password, #password_confirmation').prop('required', true);
        $('.id-pass-req').show();
        $('.id-pass-help').hide();

        $('#users_is_admin').prop('checked', false);

        $('#roleDisplayWrapper').hide();
        $('#roleSelectWrapper').show();
        try { if (typeof choicesRole !== 'undefined' && choicesRole) choicesRole.setChoiceByValue(''); } catch(e) {}
        
        userModal.show();
    }

    // OPEN EDIT MODAL
    function openEditModal(id, username, email, roleUuid, status, isAdmin) {
        $('#formUser')[0].reset();
        $('#user_id').val(id);
        $('#modalUserFormTitle').text('Edit User');
        
        $('#users_user_name').val(username);
        $('#users_email').val(email);
        
        let cleanRoleUuid = $.trim(roleUuid);
        $('#current_role_uuid').val(cleanRoleUuid);

        let roleName = $('#roles_uuid option[value="' + cleanRoleUuid + '"]').text() || '-';
        $('#roleDisplayText').text(roleName);

        $('#roleDisplayWrapper').show();
        $('#roleSelectWrapper').hide();
        try { if (typeof choicesRole !== 'undefined' && choicesRole) choicesRole.setChoiceByValue(''); } catch(e) {}

        $('#status').val(status).trigger('change'); 

        $('#users_is_admin').prop('checked', isAdmin == 1);
        
        $('#password, #password_confirmation').prop('required', false);
        $('.id-pass-req').hide();
        $('.id-pass-help').show();

        userModal.show();
    }

    // SUBMIT USER FORM (CREATE/EDIT)
    function submitUserForm(e) {
        e.preventDefault();
        
        let id = $('#user_id').val();
        let url = id ? '{{ url("/users") }}/' + id : '{{ url("/users") }}';

        let selectedRole  = $('#roles_uuid').val();
        let finalRoleUuid = ($('#roleSelectWrapper').is(':visible') && selectedRole) ? selectedRole : $('#current_role_uuid').val();
        
        let data = {
            users_user_name: $('#users_user_name').val(),
            users_email: $('#users_email').val(),
            roles_uuid: finalRoleUuid,
            status: $('#status').val(),
            users_is_admin: $('#users_is_admin').is(':checked') ? 1 : 0,
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
        };

        ajaxRequest({
            url: url,
            method: 'POST',
            data: data,
            successCallback: function(res) {
                userModal.hide();
                window.dtUsers.ajax.reload(null, false);
            }
        });
    }

    // TOGGLE STATUS
    function toggleStatus(id) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengubah status pengguna ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest({
                    url: '{{ url("/users") }}/' + id + '/toggle-status',
                    method: 'POST',
                    successCallback: function(res) {
                        window.dtUsers.ajax.reload(null, false);
                    }
                });
            }
        });
    }
</script>
@endpush
