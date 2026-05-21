<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $categories = ProductCategory::orderBy('type')->orderBy('name')->get();
        return view('admin.product-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'slug'                 => 'required|string|max:100|unique:product_categories,slug',
            'type'                 => 'required|in:product,service',
            'base_commission'      => 'required|numeric|min:0|max:100',
            'affiliate_commission' => 'required|numeric|min:0|max:100',
        ]);

        ProductCategory::create($request->only('name', 'slug', 'type', 'base_commission', 'affiliate_commission'));

        return $this->success([], __('Category created successfully.'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'slug'                 => ['required', 'string', 'max:100', Rule::unique('product_categories', 'slug')->ignore($productCategory->id)],
            'type'                 => 'required|in:product,service',
            'base_commission'      => 'required|numeric|min:0|max:100',
            'affiliate_commission' => 'required|numeric|min:0|max:100',
            'status'               => 'required|in:0,1',
        ]);

        $productCategory->update($request->only('name', 'slug', 'type', 'base_commission', 'affiliate_commission', 'status'));

        return $this->success([], __('Category updated successfully.'));
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->count() > 0) {
            return $this->error([], __('Cannot delete a category that has products assigned to it.'));
        }

        $productCategory->delete();
        return $this->success([], __('Category deleted.'));
    }

    /**
     * API endpoint — returns categories with commission info for JS preview.
     */
    public function forOwner(Request $request)
    {
        $categories = ProductCategory::active()
            ->select('id', 'name', 'slug', 'type', 'base_commission', 'affiliate_commission')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $categories]);
    }
}