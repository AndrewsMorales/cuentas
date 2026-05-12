<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::orderBy('name')->withCount('fixedExpenses')->get(),
        ]);
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('categories.index')->with('status', 'Categoría creada.');
    }

    public function edit(Category $category): View
    {
        return view('categories.form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category->id));

        return redirect()->route('categories.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->isProtected()) {
            return redirect()->route('categories.index')
                ->with('error', "La categoría \"{$category->name}\" está protegida y no se puede eliminar.");
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoría eliminada.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:categories,name' . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'name'  => ['required', 'string', 'max:100', $unique],
            'icon'  => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);
    }
}
