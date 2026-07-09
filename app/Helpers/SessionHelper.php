<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use App\Models\UserActivity;

if (!function_exists('current_user_uuid')) {
    function current_user_uuid()
    {
        return Session::get('users_uuid');
    }
}

if (!function_exists('current_user_role')) {
    function current_user_role()
    {
        return Session::get('roles_code');
    }
}

if (!function_exists('current_user_name')) {
    function current_user_name()
    {
        return Session::get('users_user_name');
    }
}

if(!function_exists('is_admin')){
    function is_admin(){
        return Session::get('users_is_admin');
    }
}

if (!function_exists('logActivity')) {
    /**
     * Helper untuk insert log ke tabel user_activity
     *
     * @param string $action
     * @param string $description
     * @param string|null $userUuid (opsional, jika null akan ambil dari session aktif)
     * @return void
     */
    function logActivity($action, $description, $userUuid = null)
    {
        $uuid = $userUuid ?? current_user_uuid();
        
        // Cek jika uuid null berarti system log / unauthorized attempt (bisa disesuaikan)
        
        UserActivity::create([
            'user_activity_user_uuid' => $uuid,
            'user_activity_action' => $action,
            'user_activity_description' => $description,
            'user_activity_ip_address' => Request::ip(),
            'user_activity_create_date' => now()
        ]);
    }
}

if (!function_exists('sanitize_rupiah')) {
    function sanitize_rupiah($value)
    {
        if (is_null($value)) return 0;
        return (float) str_replace('.', '', $value);
    }
}

