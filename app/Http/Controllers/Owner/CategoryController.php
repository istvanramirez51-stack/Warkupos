<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function destroy(Category $category)
    {
        // Cegah hapus jika masih ada menu terkait
        if ($category->menus()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki menu.');
        }
        $category->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }
}