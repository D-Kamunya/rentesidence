<?php

namespace App\Services;

use App\Models\ProductOrder;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class ProductOrderService
{
    use ResponseTrait;

    public function getAllProductOrders()
    {
        $response['pageTitle'] = __('All Product Orders');
        $response['productOrders'] = ProductOrder::where('user_id', auth()->id())->with(['orderItems.product'])->latest()->get();
        $response['products'] = Product::where('owner_user_id', auth()->id())->get();
        $response['pendingProductOrders'] = ProductOrder::where('user_id', auth()->id())->pending()->get();
        $response['paidProductOrders'] = ProductOrder::where('user_id', auth()->id())->paid()->get();
        $response['cancelledProductOrders'] = ProductOrder::where('user_id', auth()->id())->cancelled()->get();
        $response['totalProductOrders'] = ProductOrder::where('user_id', auth()->id())->count();
        $response['totalPendingProductOrders'] = ProductOrder::where('user_id', auth()->id())->pending()->count();
        $response['totalCancelledProductOrders'] = ProductOrder::where('user_id', auth()->id())->cancelled()->count();
        $response['totalBankPendingProductOrders'] = ProductOrder::query()
            ->where('product_orders.user_id', auth()->id())
            ->join('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->where('gateways.slug', 'bank')
            ->where('product_orders.payment_status', PRODUCT_ORDER_STATUS_PENDING)
            ->count();
        $response['totalPaidProductOrders'] = ProductOrder::where('user_id', auth()->id())->paid()->count();
        
        return $response;
    }

    public function getAll()
    {
        $data = ProductOrder::query()
            ->where('product_orders.user_id', auth()->id())
            ->leftJoin('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', ['product_orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\ProductOrder'"))])
            ->select(['product_orders.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name'])
            ->get();

        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getAllProductOrdersData($request)
    {
        $productOrder = ProductOrder::query()
            ->where('product_orders.user_id', auth()->id())
            ->leftJoin('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', ['product_orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\ProductOrder'"))])
            ->orderByDesc('product_orders.id')
            ->select(['product_orders.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name']);

        return datatables($productOrder)
            ->addColumn('order', function ($productOrder) {
                return '<h6>' . $productOrder->order_no . '</h6>
                        <p class="font-13">' . $productOrder->name . '</p>';
            })
            ->addColumn('due_date', function ($item) {
                return $item->due_date;
            })
            ->addColumn('amount', function ($productOrder) {
                return currencyPrice(productOrderItemTotalAmount($productOrder->id));
            })
            ->addColumn('status', function ($productOrder) {
                if ($productOrder->status == PRODUCT_ORDER_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">' . __('Paid') . '</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">' . __('Pending') . '</div>';
                }
            })
            ->addColumn('gateway', function ($productOrder) {
                if ($productOrder->gatewaySlug == 'bank') {
                    return '<a href="' . getFileUrl($productOrder->folder_name, $productOrder->file_name) . '" title="' . __('Bank slip download') . '" download>' . $productOrder->gatewayTitle . '</a>';
                }
                return $productOrder->gatewayTitle;
            })
            ->addColumn('action', function ($productOrder) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($productOrder->status == PRODUCT_ORDER_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    $html .= '<button type="button" onclick="deleteItem(\'' . route('owner.productOrder.destroy', $productOrder->id) . '\', \'allProductOrderDatatable\')" class="p-1 tbl-action-btn" title="' . __('Delete') . '"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                    if ($productOrder->gatewaySlug == 'bank') {
                        $html .= '<a href="' . getFileUrl($productOrder->folder_name, $productOrder->file_name) . '"  class="p-1 tbl-action-btn" title="' . __('Bank slip download') . '" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
                        $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('Payment Status Change') . '"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                    }
                } elseif ($productOrder->status == PRODUCT_ORDER_STATUS_PAID) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['order', 'status', 'gateway', 'action'])
            ->make(true);
    }

    public function getPaidProductOrdersData($request)
    {
        $productOrder = ProductOrder::where('product_orders.user_id', auth()->id())
            ->select(['product_orders.*'])
            ->orderByDesc('product_orders.id')
            ->where('product_orders.status', PRODUCT_ORDER_STATUS_PAID);

        return datatables($productOrder)
            ->addColumn('order', function ($productOrder) {
                return '<h6>' . $productOrder->order_no . '</h6>
                        <p class="font-13">' . $productOrder->name . '</p>';
            })
            ->addColumn('due_date', function ($item) {
                return $item->due_date;
            })
            ->addColumn('amount', function ($productOrder) {
                return currencyPrice(productOrderItemTotalAmount($productOrder->id));
            })
            ->addColumn('status', function ($productOrder) {
                if ($productOrder->status == PRODUCT_ORDER_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Pending</div>';
                }
            })
            ->addColumn('action', function ($productOrder) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($productOrder->status == PRODUCT_ORDER_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    $html .= '<button type="button" onclick="deleteItem(\'' . route('owner.productOrder.destroy', $productOrder->id) . '\', \'allProductOrderDatatable\')" class="p-1 tbl-action-btn" title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                    if ($productOrder->gatewaySlug == 'bank') {
                        $html .= '<a href="' . getFileUrl($productOrder->folder_name, $productOrder->file_name) . '"  class="p-1 tbl-action-btn" title="' . __('Bank slip download') . '" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
                        $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('Payment Status Change') . '"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                    }
                } elseif ($productOrder->status == PRODUCT_ORDER_STATUS_PAID) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $productOrder->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['order', 'status', 'action'])
            ->make(true);
    }

    public function getPendingProductOrdersData($request)
    {
        $productOrder = ProductOrder::where('product_orders.user_id', auth()->id())
            ->leftJoin('products', 'product_orders.product_id', '=', 'products.id')
            ->select(['product_orders.*', 'products.name as product_name'])
            ->orderByDesc('product_orders.id')
            ->where('product_orders.status', PRODUCT_ORDER_STATUS_PENDING);
            
        return datatables($productOrder)
            ->addColumn('order', function ($order) {
                return '<h6>' . $order->order_no . '</h6>
                        <p class="font-13">' . $order->product_name . '</p>';
            })
            ->addColumn('date', function ($order) {
                return $order->created_at->format('Y-m-d');
            })
            ->addColumn('amount', function ($order) {
                return currencyPrice($order->total_amount);
            })
            ->addColumn('status', function ($order) {
                if ($order->status == PRODUCT_ORDER_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Pending</div>';
                }
            })
            ->addColumn('action', function ($order) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($order->status == PRODUCT_ORDER_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    $html .= '<button type="button" onclick="deleteItem(\'' . route('user.product_order.destroy', $order->id) . '\', \'allProductOrdersDatatable\')" class="p-1 tbl-action-btn" title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                } elseif ($order->status == PRODUCT_ORDER_STATUS_PAID) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['order', 'status', 'action'])
            ->make(true);
    }

    public function getBankPendingProductOrdersData()
    {
        $productOrder = ProductOrder::query()
            ->join('product_order_payments', 'product_orders.id', '=', 'product_order_payments.product_order_id')
            ->join('gateways', 'product_order_payments.gateway_id', '=', 'gateways.id')
            ->join('file_managers', ['product_order_payments.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\ProductOrderPayment'"))])
            ->select(['product_orders.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name'])
            ->where('gateways.slug', 'bank')
            ->where('product_orders.user_id', auth()->id())
            ->orderByDesc('product_orders.id')
            ->where('product_order_payments.payment_status', PRODUCT_ORDER_STATUS_PENDING);

        return datatables($productOrder)
            ->addColumn('order', function ($order) {
                return '<h6>' . $order->order_no . '</h6>
                        <p class="font-13">' . $order->product_name . '</p>';
            })
            ->addColumn('date', function ($order) {
                return $order->created_at->format('Y-m-d');
            })
            ->addColumn('amount', function ($order) {
                return currencyPrice($order->total_amount);
            })
            ->addColumn('gateway', function ($order) {
                if ($order->gatewaySlug == 'bank') {
                    return '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" title="Bank slip download" download>' . $order->gatewayTitle . '</a>';
                }
                return $order->gatewayTitle;
            })
            ->addColumn('status', function ($order) {
                if ($order->status == PRODUCT_ORDER_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Pending</div>';
                }
            })
            ->addColumn('action', function ($order) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($order->status == PRODUCT_ORDER_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    $html .= '<button type="button" onclick="deleteItem(\'' . route('user.product_order.destroy', $order->id) . '\', \'allProductOrdersDatatable\')" class="p-1 tbl-action-btn" title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                    if ($order->gatewaySlug == 'bank') {
                        $html .= '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" class="p-1 tbl-action-btn" title="' . __('Bank slip download') . '" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
                        $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('Payment Status Change') . '"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                    }
                } elseif ($order->status == PRODUCT_ORDER_STATUS_PAID) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('user.product_order.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['order', 'gateway', 'status', 'action'])
            ->make(true);
    }

}
