<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index()
    {
        if (Session::has('users_uuid')) {
            return redirect('/');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ], [
            'username.required' => 'Email atau Username wajib diisi',
            'password.required' => 'Password wajib diisi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $identity = $request->input('username');
        $password = $request->input('password');

        $user = User::with('role')
            ->where(function ($query) use ($identity) {
                $query->where('users_email', $identity)
                      ->orWhere('users_user_name', $identity);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->users_password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email/username atau password salah',
                'errors' => [],
            ]);
        }

        if ($user->users_status == 2) {
            return response()->json([
                'status' => false,
                'message' => 'Akun Anda tidak aktif, hubungi administrator.',
                'errors' => []
            ]);
        }

        if (!$user->role) {
            return response()->json([
                'status' => false,
                'message' => 'Akun Anda tidak memiliki role yang valid.',
                'errors' => []
            ]);
        }

        Session::put([
            'users_uuid' => $user->users_uuid,
            'users_roles_uuid' => $user->users_roles_uuid,
            'roles_code' => $user->role->roles_code,
            'roles_name' => $user->role->roles_name,
            'users_user_name' => $user->users_user_name,
            'users_email' => $user->users_email,
            'users_is_admin' =>$user->users_is_admin
        ]);

        logActivity('LOGIN', 'User login ke sistem', $user->users_uuid);

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'data' => [
                'redirect' => url('/')
            ]
        ]);
    }

    public function logout(Request $request)
    {
        if (Session::has('users_uuid')) {
            logActivity('LOGOUT', 'User logout dari sistem');
            Session::flush();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Logout berhasil',
            'data' => [
                'redirect' => url('/login')
            ]
        ]);
    }
}
