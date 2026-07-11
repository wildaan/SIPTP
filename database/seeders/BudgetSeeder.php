<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');
        $budgets = [
            [
                'budgets_uuid' => Str::uuid()->toString(),
                'budgets_categories_uuid' => 'CAT-002', // ATK
                'budgets_period_year' => $year,
                'budgets_total_budget' => 5000000.00,
                'budgets_used_budget' => 0.00,
                'budgets_status' => 1,
            ],
            [
                'budgets_uuid' => Str::uuid()->toString(),
                'budgets_categories_uuid' => 'CAT-003', // Operasional
                'budgets_period_year' => $year,
                'budgets_total_budget' => 20000000.00,
                'budgets_used_budget' => 0.00,
                'budgets_status' => 1,
            ],
            [
                'budgets_uuid' => Str::uuid()->toString(),
                'budgets_categories_uuid' => 'CAT-004', // Marketing
                'budgets_period_year' => $year,
                'budgets_total_budget' => 15000000.00,
                'budgets_used_budget' => 0.00,
                'budgets_status' => 1,
            ]
            // PO Produk (CAT-001) sengaja tidak ada budget untuk mendemokan rule threshold > 10jt / PO
        ];

        foreach ($budgets as $budget) {
            DB::table('budgets')->insert(array_merge($budget, [
                'budgets_create_date' => now(),
            ]));
        }
    }
}
