<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GatewayService;
use App\Traits\ResponseTrait;

class ProductController extends Controller
{

        use ResponseTrait;
        public $gatewayService;

        public function __construct()
        {
            $this->gatewayService = new GatewayService;
        }
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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
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
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'type' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif',
            'delete_images' => 'array', // Add validation for images to delete
            'delete_images.*' => 'string', // Ensure each item in delete_images is a string (the path of the image)
        ]);

        // Update the product fields (excluding images for now)
        $product->update($request->except(['images', 'delete_images']));

        // Decode existing images if they exist
        $existingImages = json_decode($product->images, true) ?: [];

        // Handle image deletion
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $deleteImage) {
                // Check if the image exists in the existing images array
                if (($key = array_search($deleteImage, $existingImages)) !== false) {
                    // Remove the image from the array
                    unset($existingImages[$key]);

                    // Delete the image file from the storage
                    \Storage::disk('public')->delete($deleteImage);
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store each new image and add its path to the existing images array
                $existingImages[] = $image->store('products', 'public');
            }
        }

        // Store the combined remaining and new image paths as a JSON array in the 'images' column
        $product->images = json_encode(array_values($existingImages));

        // Save the product with the updated images
        $product->save();

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

        // Retrieve records from the ProductOrder model
        $latestMpesaProductOrder = ProductOrder::whereNotNull('payment_id')
            ->where('user_id', $ownerId) // Filter by user_id
            ->latest() // Order by created_at in descending order
            ->first(); // Retrieve only the latest record
        // Handle any pending mpesa product order transactions
        if($latestMpesaProductOrder && strpos($latestMpesaProductOrder->payment_id, 'ws') === 0 && $latestMpesaProductOrder->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
            $paymentCheck = PaymentCheck::where('products_payment_id', $latestMpesaProductOrder->id)->first();
            if (!$paymentCheck) {
                $paymentCheck = new PaymentCheck();
                $paymentCheck->products_payment_id = $latestMpesaProductOrder->id;
                $paymentCheck->check_count=0;
                $paymentCheck->last_check_at=now();
                $paymentCheck->save();
                $gateway = Gateway::find($latestMpesaProductOrder->gateway_id);
                // Clear specific flash messages
                Session::forget('success');
                Session::forget('error');
                handleProductPaymentConfirmation($latestMpesaProductOrder, null, $gateway->slug, $paymentCheck);
            }else{
                if($paymentCheck->check_count < 3){
                    $gateway = Gateway::find($latestMpesaProductOrder->gateway_id);
                    // Clear specific flash messages
                    Session::forget('success');
                    Session::forget('error');
                    handleProductPaymentConfirmation($latestMpesaProductOrder, null, $gateway->slug, $paymentCheck);
                }else {
                    // Get the creation timestamp of the product order
                    $productOrderCreatedAt = $latestMpesaProductOrder->created_at;
                    // Add 5 hours to the product order creation timestamp
                    $fiveHoursAfterProductOrderCreation = $productOrderCreatedAt->copy()->addHours(5);
                    // Check if the last_check_at timestamp in the payment check is greater than or equal to 5 hours after product order creation
                    $paymentCheckLastCheck = $paymentCheck->last_check_at;
                    if ($paymentCheckLastCheck->greaterThanOrEqualTo($fiveHoursAfterProductOrderCreation)) {
                        // Last check is more than or equal to 5 hours after product order creation
                        // Your logic here
                    } else {
                        // Last check is less than 5 hours after product order creation
                        $gateway = Gateway::find($latestMpesaProductOrder->gateway_id);
                        // Clear specific flash messages
                        Session::forget('success');
                        Session::forget('error');
                        handleProductPaymentConfirmation($latestMpesaProductOrder, null,$gateway->slug, $paymentCheck);
                    }
                }
            }
        }
    
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

    //show products in single product page
    public function show($id){
        $product = Product::findOrFail($id);
        return view('tenant.products.show', compact('product'));
    }

    //Product payment page controller
    public function pay($id){
        $data['pageTitle'] = __('Products Pay');

        $data['navMarketPlaceMMActiveClass'] = 'mm-active';
        $data['navMarketPlaceActiveClass'] = 'active';
        $data['gateways'] = $this->gatewayService->getActiveAll(auth()->user()->owner_user_id);
        $data['banks'] = $this->gatewayService->getActiveBanks();
        $data['mpesaAccounts'] = $this->gatewayService->getActiveMpesaAccounts();
        return view('tenant.products.pay', $data);
    }
}
