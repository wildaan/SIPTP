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

        $atk         = DB::table('categories')->where('categories_code', 'ATK')->first();
        $operasional = DB::table('categories')->where('categories_code', 'OP')->first();
        $budgets = [
            [
                'budgets_uuid'             => (string) Str::uuid(),
                'budgets_categories_uuid'  => $atk->categories_uuid,
                'budgets_period_year'      => $year,
                'budgets_total_budget'     => 5000000.00,
                'budgets_used_budget'      => 0.00,
                'budgets_status'           => 1,
            ],
            [
                'budgets_uuid'             => (string) Str::uuid(),
                'budgets_categories_uuid'  => $operasional->categories_uuid,
                'budgets_period_year'      => $year,
                'budgets_total_budget'     => 20000000.00,
                'budgets_used_budget'      => 0.00,
                'budgets_status'           => 1,
            ],
        ];

        foreach ($budgets as $budget) {
            DB::table('budgets')->insert(array_merge($budget, [
                'budgets_create_date' => now(),
            ]));
        }
    }
}
