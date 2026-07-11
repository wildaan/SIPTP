<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Support\Facades\Session;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $budgets = Budget::with('category')
            ->where('budgets_period_year', $year)
            ->orderBy('budgets_create_date', 'desc')
            ->get();

        $categories = Category::where('categories_status', 1)->get();

        logActivity('VIEW_BUDGETS', "Melihat daftar alokasi budget tahun {$year}");

        return view('budgets.index', compact('budgets', 'categories', 'year'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'budgets_total_budget' => sanitize_rupiah($request->budgets_total_budget)
        ]);

        $request->validate([
            'budgets_categories_uuid' => 'required|exists:categories,categories_uuid',
            'budgets_period_year'     => 'required|integer|min:2020|max:2100',
            'budgets_total_budget'    => 'required|numeric|min:0'
        ]);

        $existing = Budget::where('budgets_categories_uuid', $request->budgets_categories_uuid)
            ->where('budgets_period_year', $request->budgets_period_year)
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => false,
                'message' => 'Budget untuk kategori dan tahun ini sudah ada.'
            ], 422);
        }

        Budget::create([
            'budgets_categories_uuid' => $request->budgets_categories_uuid,
            'budgets_period_year'     => $request->budgets_period_year,
            'budgets_total_budget'    => $request->budgets_total_budget,
            'budgets_used_budget'     => 0,
            'budgets_create_by'       => Session::get('users_uuid'),
            'budgets_status'          => 1
        ]);

        $category = Category::where('categories_uuid', $request->budgets_categories_uuid)->first();
        $catName = $category ? $category->categories_name : '-';
        logActivity('CREATE_BUDGET', "Menambahkan alokasi budget untuk kategori {$catName} tahun {$request->budgets_period_year} sebesar Rp " . number_format($request->budgets_total_budget, 0, ',', '.'));

        return response()->json([
            'status'  => true,
            'message' => 'Budget berhasil ditambahkan.',
            'year'    => $request->budgets_period_year
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $budget = Budget::where('budgets_uuid', $uuid)->firstOrFail();

        $request->merge([
            'budgets_total_budget' => sanitize_rupiah($request->budgets_total_budget)
        ]);

        $request->validate([
            'budgets_total_budget' => 'required|numeric|min:0'
        ]);

        if ($request->budgets_total_budget < $budget->budgets_used_budget) {
            return response()->json([
                'status'  => false,
                'message' => 'Total budget tidak boleh lebih kecil dari budget yang sudah terpakai (Rp ' . number_format($budget->budgets_used_budget, 0, ',', '.') . ').'
            ], 422);
        }

        $budget->update([
            'budgets_total_budget' => $request->budgets_total_budget,
            'budgets_update_by'    => Session::get('users_uuid')
        ]);

        $catName = $budget->category ? $budget->category->categories_name : '-';
        logActivity('UPDATE_BUDGET', "Mengubah total budget kategori {$catName} tahun {$budget->budgets_period_year} menjadi Rp " . number_format($request->budgets_total_budget, 0, ',', '.'));

        return response()->json([
            'status'  => true,
            'message' => 'Total budget berhasil diubah.',
            'year'    => $budget->budgets_period_year
        ]);
    }
}
