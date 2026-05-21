<?php

namespace App\Services;

use App\Models\ProductOrder;
use App\Models\Product;
use App\Models\Owner;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductOrderService
{
    use ResponseTrait;

    private function ownerOrdersQuery()
    {
        $ownerId = Owner::where('user_id', auth()->id())->value('id');

        return ProductOrder::whereHas('orderItems.product', function ($q) use ($ownerId) {
            $q->where('owner_user_id', $ownerId);
        });
    }

    public function getAllProductOrders(Request $request)
    {
        $ownerId   = Owner::where('user_id', auth()->id())->value('id');
        $status    = $request->input('status', 'all');
        $search    = $request->input('search', '');
        $perPage   = 15;
    
        $query = ProductOrder::whereHas('orderItems.product', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })
            ->with(['orderItems.product', 'user.tenant.property', 'user.tenant.unit'])
            ->latest();
    
        // Status filter (dual-scope)
        $statusMap = [
            'order_pending'     => 'orderPending',
            'order_completed'   => 'orderCompleted',
            'order_cancelled'   => 'orderCancelled',
            'payment_pending'   => 'paymentPending',
            'payment_paid'      => 'paymentPaid',
            'payment_cancelled' => 'paymentCancelled',
        ];
    
        if (isset($statusMap[$status])) {
            $scope = $statusMap[$status];
            $query->$scope();
        }
    
        // Search — order ID or product name
        if ($search !== '') {
            $query->where(function ($q) use ($search, $ownerId) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhereHas('orderItems.product', function ($q2) use ($search, $ownerId) {
                      $q2->where('owner_user_id', $ownerId)
                         ->where('name', 'like', "%{$search}%");
                  });
            });
        }
    
        $response['pageTitle']      = __('All Product Orders');
        $response['productOrders']  = $query->paginate($perPage)->withQueryString();
        $response['products']       = Product::where('owner_user_id', $ownerId)->get();
        $response['activeStatus']   = $status;
        $response['search']         = $search;
    
        // Summary counts (split by order vs payment)
        $response['totalProductOrders']          = $this->ownerOrdersQuery()->count();
    
        // Order lifecycle counts
        $response['totalPendingProductOrders']   = $this->ownerOrdersQuery()->orderPending()->count();
        $response['totalCompleteProductOrders']  = $this->ownerOrdersQuery()->orderCompleted()->count();
        $response['totalCancelledProductOrders'] = $this->ownerOrdersQuery()->orderCancelled()->count();
    
        // Payment state counts
        $response['totalPaymentPending']         = $this->ownerOrdersQuery()->paymentPending()->count();
        $response['totalPaymentPending']         = $this->ownerOrdersQuery()->paymentPaid()->count();
        $response['totalPaymentCancelled']       = $this->ownerOrdersQuery()->paymentCancelled()->count();
    
        // Bank-specific pending payments
        $response['totalBankPendingProductOrders'] = $this->ownerOrdersQuery()
            ->join('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->where('gateways.slug', 'bank')
            ->where('product_orders.payment_status', PRODUCT_ORDER_STATUS_PENDING)
            ->count();
    
        return $response;
    }
    

    public function getAll()
    {
        $data = $this->ownerOrdersQuery()
            ->leftJoin('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', [
                'product_orders.deposit_slip_id' => 'file_managers.id',
                'file_managers.origin_type'      => DB::raw("'App\\\\Models\\\\ProductOrder'"),
            ])
            ->select([
                'product_orders.*',
                'gateways.title as gatewayTitle',
                'gateways.slug as gatewaySlug',
                'file_managers.file_name',
                'file_managers.folder_name',
            ])
            ->get();

        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getAllProductOrdersData($request)
    {
        $productOrder = $this->ownerOrdersQuery()
            ->join('product_order_items', 'product_orders.id', '=', 'product_order_items.product_order_id')
            ->join('products', 'product_order_items.product_id', '=', 'products.id')
            ->leftJoin('gateways', 'product_orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', [
                'product_orders.deposit_slip_id' => 'file_managers.id',
                'file_managers.origin_type'      => DB::raw("'App\\\\Models\\\\ProductOrder'"),
            ])
            ->select([
                'product_orders.*',
                'gateways.title as gatewayTitle',
                'gateways.slug as gatewaySlug',
                'file_managers.file_name',
                'file_managers.folder_name',
            ])
            ->distinct()
            ->orderByDesc('product_orders.id');

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
        $productOrder = $this->ownerOrdersQuery()
            ->join('product_order_items', 'product_orders.id', '=', 'product_order_items.product_order_id')
            ->join('products', 'product_order_items.product_id', '=', 'products.id')
            ->select(['product_orders.*'])
            ->distinct()
            ->orderByDesc('product_orders.id')
            ->where('product_orders.payment_status', PRODUCT_ORDER_STATUS_PAID);

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
        $productOrder = $this->ownerOrdersQuery()
            ->join('product_order_items', 'product_orders.id', '=', 'product_order_items.product_order_id')
            ->join('products', 'product_order_items.product_id', '=', 'products.id')
            ->select(['product_orders.*', 'products.name as product_name'])
            ->distinct()
            ->orderByDesc('product_orders.id')
            ->where('product_orders.payment_status', PRODUCT_ORDER_STATUS_PENDING);

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
                if ($order->payment_status == PRODUCT_ORDER_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Pending</div>';
                }
            })
            ->addColumn('action', function ($order) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($order->payment_status == PRODUCT_ORDER_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    $html .= '<button type="button" onclick="deleteItem(\'' . route('owner.productOrder.destroy', $order->id) . '\', \'allProductOrdersDatatable\')" class="p-1 tbl-action-btn" title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                } elseif ($order->payment_status == PRODUCT_ORDER_STATUS_PAID) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['order', 'status', 'action'])
            ->make(true);
    }

    // public function getBankPendingProductOrdersData()
    // {
    //     $productOrder = $this->ownerOrdersQuery()
    //         ->join('product_order_payments', 'product_orders.id', '=', 'product_order_payments.product_order_id')
    //         ->join('gateways', 'product_order_payments.gateway_id', '=', 'gateways.id')
    //         ->join('file_managers', [
    //             'product_order_payments.deposit_slip_id' => 'file_managers.id',
    //             'file_managers.origin_type'              => DB::raw("'App\\\\Models\\\\ProductOrderPayment'"),
    //         ])
    //         ->select([
    //             'product_orders.*',
    //             'gateways.title as gatewayTitle',
    //             'gateways.slug as gatewaySlug',
    //             'file_managers.file_name',
    //             'file_managers.folder_name',
    //         ])
    //         ->where('gateways.slug', 'bank')
    //         ->where('product_order_payments.payment_status', PRODUCT_ORDER_STATUS_PENDING)
    //         ->distinct()
    //         ->orderByDesc('product_orders.id');

    //     return datatables($productOrder)
    //         ->addColumn('order', function ($order) {
    //             return '<h6>' . $order->order_no . '</h6>
    //                     <p class="font-13">' . $order->product_name . '</p>';
    //         })
    //         ->addColumn('date', function ($order) {
    //             return $order->created_at->format('Y-m-d');
    //         })
    //         ->addColumn('amount', function ($order) {
    //             return currencyPrice($order->total_amount);
    //         })
    //         ->addColumn('gateway', function ($order) {
    //             if ($order->gatewaySlug == 'bank') {
    //                 return '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" title="Bank slip download" download>' . $order->gatewayTitle . '</a>';
    //             }
    //             return $order->gatewayTitle;
    //         })
    //         ->addColumn('status', function ($order) {
    //             if ($order->payment_status == PRODUCT_ORDER_STATUS_PAID) {
    //                 return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
    //             } else {
    //                 return '<div class="status-btn status-btn-orange font-13 radius-4">Pending</div>';
    //             }
    //         })
    //         ->addColumn('action', function ($order) {
    //             $html = '<div class="tbl-action-btns d-inline-flex">';
    //             if ($order->payment_status == PRODUCT_ORDER_STATUS_PENDING) {
    //                 $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
    //                 $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
    //                 $html .= '<button type="button" onclick="deleteItem(\'' . route('owner.productOrder.destroy', $order->id) . '\', \'allProductOrdersDatatable\')" class="p-1 tbl-action-btn" title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
    //                 if ($order->gatewaySlug == 'bank') {
    //                     $html .= '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" class="p-1 tbl-action-btn" title="' . __('Bank slip download') . '" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
    //                     $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('Payment Status Change') . '"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
    //                 }
    //             } elseif ($order->payment_status == PRODUCT_ORDER_STATUS_PAID) {
    //                 $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.productOrder.details', $order->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
    //             }
    //             $html .= '</div>';
    //             return $html;
    //         })
    //         ->rawColumns(['order', 'gateway', 'status', 'action'])
    //         ->make(true);
    // }
}