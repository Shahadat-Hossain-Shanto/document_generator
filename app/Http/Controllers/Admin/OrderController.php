<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getAdminOrders(Request $request)
    {
        $query = Order::with('service', 'referralCode:id,code');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('stripe_transaction_id', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('state', 'LIKE', "%{$search}%")

                    ->orWhereHas('service', function ($sq) use ($search) {
                        $sq->where('title', 'LIKE', "%{$search}%");
                    })

                    ->orWhereHas('referralCode', function ($rq) use ($search) {
                        $rq->where('code', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status != 'All') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_filter')) {
            $now = Carbon::now();
            if ($request->date_filter == 'this_month') {
                $query->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year);
            } elseif ($request->date_filter == 'last_month') {
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year);
            } elseif ($request->date_filter == 'this_year') {
                $query->whereYear('created_at', $now->year);
            }
        }

        try {
            $perPage = $request->get('limit', 10);
            $page = $request->get('current_page', 1);

            $orders = $query->latest()->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'total' => $orders->total(),
                'limit' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // public function getAdminOrders(Request $request)
    // {
    //     $query = Order::with('service');

    //     if ($request->filled('search')) {
    //         $search = $request->search;

    //         $query->where(function ($q) use ($search) {
    //             $q->where('order_number', 'LIKE', "%{$search}%")
    //                 ->orWhere('stripe_transaction_id', 'LIKE', "%{$search}%")
    //                 ->orWhere('customer_name', 'LIKE', "%{$search}%")
    //                 ->orWhere('state', 'LIKE', "%{$search}%")

    //                 ->orWhereHas('service', function ($sq) use ($search) {
    //                     $sq->where('title', 'LIKE', "%{$search}%");
    //                 });
    //         });
    //     }

    //     if ($request->filled('status') && $request->status != 'All') {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('date_filter')) {
    //         $now = Carbon::now();
    //         if ($request->date_filter == 'this_month') {
    //             $query->whereMonth('created_at', $now->month)
    //                 ->whereYear('created_at', $now->year);
    //         } elseif ($request->date_filter == 'last_month') {
    //             $lastMonth = Carbon::now()->subMonth();
    //             $query->whereMonth('created_at', $lastMonth->month)
    //                 ->whereYear('created_at', $lastMonth->year);
    //         } elseif ($request->date_filter == 'this_year') {
    //             $query->whereYear('created_at', $now->year);
    //         }
    //     }

    //     try {
    //         $perPage = $request->get('limit', 10);
    //         $page = $request->get('current_page', 1);

    //         $orders = $query->latest()->paginate($perPage, ['*'], 'page', $page);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $orders->items(),
    //             'total' => $orders->total(),
    //             'limit' => $orders->perPage(),
    //             'current_page' => $orders->currentPage(),
    //             'last_page' => $orders->lastPage(),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getOrderDetail($id)
    {
        $order = Order::with(['service:id,title,price', 'referralCode:id,code'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $serviceName = $order->service_name ?? ($order->service ? $order->service->title : 'N/A');
        $servicePrice = $order->service ? $order->service->price : null;
        $order->unsetRelation('service');
        $order->service_name = $serviceName;
        $order->service_price = $servicePrice;

        return response()->json([
            'success' => true,
            'data' => $order->makeHidden(['created_at', 'updated_at', 'referral_code_id'])
        ]);
    }
}
