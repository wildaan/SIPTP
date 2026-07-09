<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('roles_status', 1)->get();
        return view('users.index', compact('roles'));
    }

    public function list(Request $request)
    {
        $query = User::with('role');
        $recordsTotal = User::count();

        // Datatables Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                // There is no users_name column based on the DB schema previously checked (users_user_name, users_email)
                $q->where('users_user_name', 'like', "%{$search}%")
                  ->orWhere('users_email', 'like', "%{$search}%");
            });
        }

        // Custom Filters
        if ($request->filled('role')) {
            $query->where('users_roles_uuid', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('users_status', $request->status);
        }

        $recordsFiltered = $query->count();

        // Datatables Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        // Order
        $query->orderBy('users_id', 'desc');

        $users = $query->get();
        $data = [];

        foreach ($users as $user) {
            $roleName = $user->role ? $user->role->roles_name : '-';
            $statusBadge = $user->users_status == 1 
                ? '<span class="badge bg-success">Aktif</span>' 
                : '<span class="badge bg-danger">Tidak Aktif</span>';

            $toggleBtn = $user->users_status == 1
                ? '<button class="btn btn-sm btn-danger me-1" title="Nonaktifkan" onclick="toggleStatus(\''.$user->users_id.'\')"><i class="bi bi-person-x-fill"></i></button>'
                : '<button class="btn btn-sm btn-success me-1" title="Aktifkan" onclick="toggleStatus(\''.$user->users_id.'\')"><i class="bi bi-person-check-fill"></i></button>';

            $actionButtons = '
                <div class="text-center">
                    <button class="btn btn-sm btn-info me-1" title="Edit"
                        onclick="openEditModal(\''.$user->users_id.'\', \''.$user->users_user_name.'\', \''.$user->users_email.'\', \''.$user->users_roles_uuid.'\', \''.$user->users_status.'\')">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    '.$toggleBtn.'
                    <button class="btn btn-sm btn-warning" title="Reset Password" onclick="openResetPasswordModal(\''.$user->users_id.'\', \''.$user->users_user_name.'\')">
                        <i class="bi bi-key-fill"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'users_user_name' => '<span class="font-bold">'.$user->users_user_name.'</span>',
                'users_email' => $user->users_email,
                'role' => '<span class="badge bg-light-primary text-primary">'.$roleName.'</span>',
                'status' => $statusBadge,
                'action' => $actionButtons
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'users_user_name' => 'required|string|unique:users,users_user_name',
            'users_email' => 'required|email|unique:users,users_email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
            'roles_uuid' => 'required|exists:roles,roles_uuid',
            'status' => 'nullable|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'users_uuid' => Str::uuid()->toString(),
            'users_roles_uuid' => $request->roles_uuid,
            'users_email' => $request->users_email,
            'users_user_name' => $request->users_user_name,
            'users_password' => Hash::make($request->password),
            'users_status' => $request->status ?? 1,
            'users_create_by' => current_user_uuid(),
            'users_create_date' => now()
        ]);

        logActivity('CREATE_USER', "Menambahkan user {$user->users_user_name}");

        return response()->json([
            'status' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => []
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'users_user_name' => 'required|string|unique:users,users_user_name,' . $id . ',users_id',
            'users_email' => 'required|email|unique:users,users_email,' . $id . ',users_id',
            'roles_uuid' => 'required|exists:roles,roles_uuid',
            'status' => 'required|in:1,2',
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'users_roles_uuid' => $request->roles_uuid,
            'users_email' => $request->users_email,
            'users_user_name' => $request->users_user_name,
            'users_status' => $request->status,
            'users_update_by' => current_user_uuid(),
            'users_update_date' => now()
        ];

        if ($request->filled('password')) {
            $updateData['users_password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        logActivity('UPDATE_USER', "Mengubah data user {$user->users_user_name}");

        return response()->json([
            'status' => true,
            'message' => 'User berhasil diperbarui',
            'data' => []
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->users_uuid === current_user_uuid()) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak dapat mengubah status Anda sendiri.',
                'errors' => []
            ], 403);
        }

        $newStatus = $user->users_status == 1 ? 2 : 1;
        $user->update([
            'users_status' => $newStatus,
            'users_update_by' => current_user_uuid(),
            'users_update_date' => now()
        ]);

        $statusText = $newStatus == 1 ? 'diaktifkan' : 'dinonaktifkan';
        logActivity('TOGGLE_STATUS_USER', "User {$user->users_user_name} telah {$statusText}");

        return response()->json([
            'status' => true,
            'message' => "User berhasil {$statusText}",
            'data' => []
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'users_password' => Hash::make($request->password),
            'users_update_by' => current_user_uuid(),
            'users_update_date' => now()
        ]);

        logActivity('RESET_PASSWORD_USER', "Password user {$user->users_user_name} telah direset");

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset',
            'data' => []
        ]);
    }
}
