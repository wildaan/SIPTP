<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Session;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('categories_create_date', 'desc')->get();
        logActivity('VIEW_CATEGORIES', 'Melihat daftar kategori');
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'categories_name' => 'required|string|max:100',
            'categories_code' => 'required|string|max:20|unique:categories,categories_code'
        ]);

        Category::create([
            'categories_name'      => $request->categories_name,
            'categories_code'      => strtoupper($request->categories_code),
            'categories_create_by' => Session::get('users_uuid'),
            'categories_status'    => 1
        ]);

        logActivity('CREATE_CATEGORY', "Menambahkan kategori baru: {$request->categories_name} ({$request->categories_code})");

        return response()->json([
            'status'  => true,
            'message' => 'Kategori berhasil ditambahkan.'
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $category = Category::where('categories_uuid', $uuid)->firstOrFail();

        $request->validate([
            'categories_name' => 'required|string|max:100',
            'categories_code' => 'required|string|max:20|unique:categories,categories_code,' . $category->categories_id . ',categories_id'
        ]);

        $category->update([
            'categories_name'      => $request->categories_name,
            'categories_code'      => strtoupper($request->categories_code),
            'categories_update_by' => Session::get('users_uuid')
        ]);

        logActivity('UPDATE_CATEGORY', "Mengubah kategori: {$category->categories_name} menjadi {$request->categories_name} ({$request->categories_code})");

        return response()->json([
            'status'  => true,
            'message' => 'Kategori berhasil diubah.'
        ]);
    }

    public function toggleStatus($uuid)
    {
        $category = Category::where('categories_uuid', $uuid)->firstOrFail();

        $newStatus = $category->categories_status == 1 ? 2 : 1;
        $category->update([
            'categories_status'    => $newStatus,
            'categories_update_by' => Session::get('users_uuid')
        ]);

        $statusName = $newStatus == 1 ? 'diaktifkan' : 'dinonaktifkan';

        logActivity('TOGGLE_STATUS_CATEGORY', "Mengubah status kategori {$category->categories_name} menjadi " . ($newStatus == 1 ? 'Aktif' : 'Non-Aktif'));

        return response()->json([
            'status'  => true,
            'message' => "Kategori berhasil {$statusName}."
        ]);
    }
}
