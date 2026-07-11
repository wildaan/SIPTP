<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'users_uuid' => Str::uuid()->toString(),
                'users_roles_uuid' => 'd34797d4-f3cb-441d-9224-56f691cf5b05',
                'users_email' => 'staff@test.com',
                'users_user_name' => 'Staff',
                'users_status' => 1,
                'users_is_admin' => 1,
                'users_password' => $password,
            ],
            [
                'users_uuid' => Str::uuid()->toString(),
                'users_roles_uuid' => 'c99f2b65-7539-4b01-acfb-51892b1fc129',
                'users_email' => 'spv@test.com',
                'users_user_name' => 'Supervisor',
                'users_status' => 1,
                'users_is_admin' => 0,
                'users_password' => $password,
            ],
            [
                'users_uuid' => Str::uuid()->toString(),
                'users_roles_uuid' => '323c5850-c286-4983-844e-92643fd246bb',
                'users_email' => 'manager@test.com',
                'users_user_name' => 'Manager',
                'users_status' => 1,
                'users_is_admin' => 0,
                'users_password' => $password,
            ],
            [
                'users_uuid' => Str::uuid()->toString(),
                'users_roles_uuid' => '75484b9e-b9cc-4a6a-ace1-3df13d1ebb94',
                'users_email' => 'direktur@test.com',
                'users_user_name' => 'Direktur',
                'users_status' => 1,
                'users_is_admin' => 0,
                'users_password' => $password,
            ],
            [
                'users_uuid' => Str::uuid()->toString(),
                'users_roles_uuid' => '6833ae25-4f31-414b-ae47-4a393f344686',
                'users_email' => 'finance@test.com',
                'users_user_name' => 'Finance',
                'users_status' => 1,
                'users_is_admin' => 0,
                'users_password' => $password,
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->insert(array_merge($user, [
                'users_create_date' => now(),
            ]));
        }
    }
}
