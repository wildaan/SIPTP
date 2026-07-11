<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('roles_status', 1)->get();
        logActivity('VIEW_USERS', 'Melihat halaman kelola user');
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
                        onclick="openEditModal(\''.$user->users_id.'\', \''.$user->users_user_name.'\', \''.$user->users_email.'\', \''.$user->users_roles_uuid.'\', \''.$user->users_status.'\', \''.($user->users_is_admin ?? 0).'\')">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    '.$toggleBtn.'
                </div>
            ';
            // <button class="btn btn-sm btn-warning" title="Reset Password" onclick="openResetPasswordModal(\''.$user->users_id.'\', \''.$user->users_user_name.'\')">
            //     <i class="bi bi-key-fill"></i>
            // </button>
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
            //'users_status' => $request->status ?? 1,
            'users_is_admin' => $request->users_is_admin ?? 0,
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
            //'status' => 'required|in:1,2',
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
            //'users_status' => $request->status,
            'users_is_admin' => $request->users_is_admin ?? 0,
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

    public function updateProfile(Request $request)
    {
        $userUuid = current_user_uuid();
        $user = User::where('users_uuid', $userUuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'users_user_name' => 'required|string|unique:users,users_user_name,' . $user->users_id . ',users_id',
            'users_email'     => 'required|email|unique:users,users_email,' . $user->users_id . ',users_id',
            'password'        => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|same:password',
        ], [
            'users_user_name.required' => 'Username wajib diisi.',
            'users_user_name.unique'   => 'Username sudah digunakan.',
            'users_email.required'     => 'Email wajib diisi.',
            'users_email.email'        => 'Format email tidak valid.',
            'users_email.unique'       => 'Email sudah digunakan.',
            'password.min'             => 'Password minimal 8 karakter.',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'users_user_name' => $request->users_user_name,
            'users_email'     => $request->users_email,
            'users_update_by' => $userUuid,
            'users_update_date' => now()
        ];

        if ($request->filled('password')) {
            $updateData['users_password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        Session::put('users_user_name', $request->users_user_name);
        Session::put('users_email', $request->users_email);

        logActivity('UPDATE_PROFILE', "User mengubah profil sendiri");

        return response()->json([
            'status' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => []
        ]);
    }

    public function auditTrail()
    {
        if (is_admin() != 1) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        logActivity('VIEW_AUDIT_TRAIL', 'Melihat halaman audit trail');

        return view('users.audit-trail');
    }

    public function auditTrailList(Request $request)
    {
        if (is_admin() != 1) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = UserActivity::with('user');
        $recordsTotal = UserActivity::count();

        // Datatables Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('user_activity_action', 'like', "%{$search}%")
                  ->orWhere('user_activity_description', 'like', "%{$search}%")
                  ->orWhere('user_activity_ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('users_user_name', 'like', "%{$search}%");
                  });
            });
        }

        $recordsFiltered = $query->count();

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $query->orderBy('user_activity_create_date', 'desc');

        $activities = $query->get();
        $data = [];

        foreach ($activities as $activity) {
            $data[] = [
                'user_name'   => $activity->user->users_user_name ?? 'System',
                'action'      => '<span class="badge bg-light-primary text-primary">' . $activity->user_activity_action . '</span>',
                'description' => $activity->user_activity_description,
                'ip_address'  => $activity->user_activity_ip_address,
                'created_at'  => date('d M Y H:i:s', strtotime($activity->user_activity_create_date)),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}
