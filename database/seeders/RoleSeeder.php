<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'roles_uuid' => 'd34797d4-f3cb-441d-9224-56f691cf5b05',
                'roles_code' => 'staff',
                'roles_name' => 'STAFF',
                'roles_status' => 1,
            ],
            [
                'roles_uuid' => 'c99f2b65-7539-4b01-acfb-51892b1fc129',
                'roles_code' => 'spv',
                'roles_name' => 'SPV',
                'roles_status' => 1,
            ],
            [
                'roles_uuid' => '323c5850-c286-4983-844e-92643fd246bb',
                'roles_code' => 'manager',
                'roles_name' => 'MANAGER',
                'roles_status' => 1,
            ],
            [
                'roles_uuid' => '75484b9e-b9cc-4a6a-ace1-3df13d1ebb94',
                'roles_code' => 'direktur',
                'roles_name' => 'DIREKTUR',
                'roles_status' => 1,
            ],
            [
                'roles_uuid' => '6833ae25-4f31-414b-ae47-4a393f344686',
                'roles_code' => 'finance',
                'roles_name' => 'FINANCE',
                'roles_status' => 1,
            ]
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'roles_create_date' => now(),
            ]));
        }
    }
}
