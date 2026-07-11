<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'categories_uuid'   => (string) Str::uuid(),
                'categories_name'   => 'PO Produk',
                'categories_code'   => 'PO',
                'categories_status' => 1,
            ],
            [
                'categories_uuid'   => (string) Str::uuid(),
                'categories_name'   => 'PO Produk - Aset Kantor',
                'categories_code'   => 'PO-ASET',
                'categories_status' => 1,
            ],
            [
                'categories_uuid'   => (string) Str::uuid(),
                'categories_name'   => 'ATK',
                'categories_code'   => 'ATK',
                'categories_status' => 1,
            ],
            [
                'categories_uuid'   => (string) Str::uuid(),
                'categories_name'   => 'Operasional',
                'categories_code'   => 'OP',
                'categories_status' => 1,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'categories_create_date' => now(),
            ]));
        }
    }
}
