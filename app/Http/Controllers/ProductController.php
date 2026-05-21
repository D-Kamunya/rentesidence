<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\PaymentCheck;
use App\Models\Gateway;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GatewayService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{

    use ResponseTrait;
    public $gatewayService;

    public function __construct()
    {
        $this->gatewayService = new GatewayService;
    }
    // Start filter
    public function index(Request $request, SubscriptionService $subscriptionService)
    {
        // Find the owner record linked to the current user
        $owner = Owner::where('user_id', Auth::id())->firstOrFail();

        // Query products belonging to this owner
        $query = Product::where('owner_user_id', $owner->id);

        // Retrieve any filter values from the request
        $type = $request->input('type');
        $category = $request->input('category');

        // Display the earnings
        $currentPlan     = $subscriptionService->getCurrentPlan();
        $packageMarkup   = $currentPlan?->commission_markup   ?? 3.0;
        $packageDiscount = $currentPlan?->commission_discount ?? 0.0;

        // Apply type filter if selected
        if (!empty($type)) {
            $query->where('type', $type);
        }

        // Apply category filter if selected
        if (!empty($category)) {
            $query->where('category', $category);
        }

        // Paginate the results with a limit of 10 per page
        $products = $query
            ->with('productCategory')
            ->paginate(10)
            ->appends([
                'type' => $type,
                'category' => $category,
            ]);

        // Pass the products and filter values to the view
        return view('owner.products.index', compact('products', 'type', 'category', 'packageMarkup', 'packageDiscount'));
    }

    // For owners to create a new product/service

    public function create(SubscriptionService $subscriptionService)
    {
        $currentPlan     = $subscriptionService->getCurrentPlan();
        $packageMarkup   = $currentPlan?->commission_markup   ?? 3.0;
        $packageDiscount = $currentPlan?->commission_discount ?? 0.0;
    
        return view('owner.products.create', compact('packageMarkup', 'packageDiscount'));
    }

    // Store new product/service
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric',
            'category'            => 'required|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'type'                => 'required|string',
            'images'              => 'required|array|min:1',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);
    
        $product = new Product($request->except('images'));
    
        $owner = Owner::where('user_id', Auth::id())->firstOrFail();
        $product->owner_user_id      = $owner->id;
        $product->product_category_id = $request->product_category_id;
    
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
        if (!empty($imagePaths)) {
            $product->images = json_encode($imagePaths);
        }
    
        $product->save();
    
        return redirect()->route('owner.products.index')
            ->with('success', 'Product created successfully.');
    }

    // Edit existing product/service
    public function edit(Product $product, SubscriptionService $subscriptionService)
    {
        $currentPlan     = $subscriptionService->getCurrentPlan();
        $packageMarkup   = $currentPlan?->commission_markup   ?? 3.0;
        $packageDiscount = $currentPlan?->commission_discount ?? 0.0;
    
        return view('owner.products.edit', compact('product', 'packageMarkup', 'packageDiscount'));
    }

    // Update product/service
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric',
            'category'            => 'required|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'type'                => 'required|string',
            'images.*'            => 'image|mimes:jpeg,png,jpg,gif',
            'delete_images'       => 'array',
            'delete_images.*'     => 'string',
        ]);
    
        $product->update($request->except(['images', 'delete_images']));
        $product->product_category_id = $request->product_category_id;
    
        $existingImages = json_decode($product->images, true) ?: [];
    
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $deleteImage) {
                if (($key = array_search($deleteImage, $existingImages)) !== false) {
                    unset($existingImages[$key]);
                    Storage::disk('public')->delete($deleteImage);
                }
            }
        }
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('products', 'public');
            }
        }
    
        $product->images = json_encode(array_values($existingImages));
        $product->save();
    
        return redirect()->route('owner.products.index')
            ->with('success', 'Product updated successfully.');
    }

     // Delete product/service
    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('owner.products.index')->with('success', 'Product deleted successfully.');
    }

    // For tenants to view products/services
    public function showProductsForTenant(Request $request) {
        $tenant = Auth::user();

        // Tenant has owner_user_id referencing owners.user_id
        $owner = Owner::where('user_id', $tenant->owner_user_id)->firstOrFail();  

        // Get distinct categories for this owner's products
        $categories = Product::where('owner_user_id', $owner->id)
            ->select('category')
            ->distinct()
            ->pluck('category');

        // Query products by owner.id
        $products = Product::where('owner_user_id', $owner->id)
        ->when($request->category, function ($query) use ($request) {
            return $query->where('category', $request->category);
        })
        ->when($request->type, function ($query) use ($request) {
            return $query->where('type', $request->type);
        })
        ->paginate(10)
        ->appends($request->only('category', 'type'));

        return view('tenant.products.index', compact('products', 'categories'));
    }

    //show products in single product page
    public function show($id){
        $product = Product::findOrFail($id);
        return view('tenant.products.show', compact('product'));
    }

    //Product payment page controller
    public function pay(){
        $data['pageTitle'] = __('Products Pay');

        $data['navMarketPlaceMMActiveClass'] = 'mm-active';
        $data['navMarketPlaceActiveClass'] = 'active';
        $data['gateways'] = $this->gatewayService->getActiveAll(auth()->user()->owner_user_id);
        $data['banks'] = $this->gatewayService->getActiveBanks();
        $data['mpesaAccounts'] = $this->gatewayService->getActiveMpesaAccounts();
        return view('tenant.products.pay', $data);
    }

    public function receipt($id)
    {
        $order = ProductOrder::where('user_id', auth()->id())
            ->with(['orderItems.product', 'gateway'])
            ->findOrFail($id);
    
        return view('tenant.products.order.receipt', compact('order'));
    }
}
