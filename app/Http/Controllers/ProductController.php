<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // For owners to list their products/services
    public function index() {
        $products = Product::where('owner_user_id', Auth::id())->paginate(10);
        return view('owner.products.index', compact('products'));
    }

    // For owners to create a new product/service
    public function create() {
        return view('owner.products.create');
    }

    // Store new product/service
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = new Product($request->all());
        $product->owner_user_id = Auth::id();

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return redirect()->route('owner.products.index')->with('success', 'Product created successfully.');
    }

    // Edit existing product/service
    public function edit(Product $product) {
        return view('owner.products.edit', compact('product'));
    }

    // Update product/service
    public function update(Request $request, Product $product) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->update($request->all());

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }

        return redirect()->route('owner.products.index')->with('success', 'Product updated successfully.');
    }

    // Delete product/service
    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('owner.products.index')->with('success', 'Product deleted successfully.');
    }

    // For tenants to view products/services
    public function showProductsForTenant(Request $request) {
        $tenant = Auth::user();
        $ownerId = $tenant->owner_user_id;
    
        $products = Product::where('owner_user_id', $ownerId)
                    ->when($request->category, function ($query) use ($request) {
                        return $query->where('category', $request->category);
                    })
                    ->paginate(10);
    
        return view('tenant.products.index', compact('products'));
    }
}
