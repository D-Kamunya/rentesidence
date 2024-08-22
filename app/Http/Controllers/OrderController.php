<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Product $product) {
        $order = new Order();
        $order->tenant_id = Auth::id();
        $order->product_id = $product->id;
        $order->unit_id = Auth::user()->unit_id;
        $order->status = 'pending';
        $order->save();

        // Notify caretaker
        // Assuming you have a Notification system set up
        //$caretaker = Auth::user()->caretaker;
        //$caretaker->notify(new \App\Notifications\ProductOrdered($order));

        return redirect()->route('tenant.products.index')->with('success', 'Product/Service ordered successfully. The caretaker has been notified.');
    }
}
