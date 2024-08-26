<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
        // Start filter
        public function index(Request $request)
        {
            
            // query taking into account owner authentication
            $query = Product::where('owner_user_id', Auth::id());
        
            //Retrieving any filter values from the request
            $type = $request->input('type');
            $category = $request->input('category');
        
            // type filter if selected
            if (!empty($type)) {
                $query->where('type', $type);
            }
        
            // category filter if selected
            if (!empty($category)) {
                $query->where('category', $category);
            }
           
            // Paginate the results with a limit of 10 per page
            $products = $query->paginate(10)->appends(['type' => $type, 'category' => $category]);
        
            // Pass the products and filter values to the view
            return view('owner.products.index', compact('products', 'type', 'category'));
            
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
            'images' => 'required|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

         // creating a new product instance
        $product = new Product($request->all());
        $product->owner_user_id = Auth::id();

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
    
        // Store the image paths as a JSON array in the 'images' column
        if (!empty($imagePaths)) {
            $product->images = json_encode($imagePaths);
        }
    
        // Save the product
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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

            $product->update($request->all());

            // Handle image deletion
            if (is_string($product->images)) {
                $existingImages = json_decode($product->images, true) ?: [];
            } elseif (is_array($product->images)) {
                $existingImages = $product->images;
            } else {
                $existingImages = [];
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    // Store each new image and add its path to the existing images array
                    $existingImages[] = $image->store('products', 'public');
    }
}
            // Handle image deletion
            if ($request->has('delete_images')) {
                foreach ($request->input('delete_images') as $imageToDelete) {
            // Filter out the deleted images from the existing array
                $existingImages = array_filter($existingImages, function($image) use ($imageToDelete) {
                    return $image !== $imageToDelete;
                    });
            // Delete the image from storage
                Storage::delete('public/' . $imageToDelete);
                }   
            }

            // Update the images field in the database
                $product->images = json_encode(array_values($existingImages));
            
            // Save the product
                $product->save();

            // Redirect with success message
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
                ->when($request->type, function ($query) use ($request) {
                    return $query->where('type', $request->type);
                })
                ->paginate(10)->appends($request->only('category', 'type')); // To retain filters in pagination links
    
        return view('tenant.products.index', compact('products'));
    }
}
