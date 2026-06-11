<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getMetrics()
    {
        $totalRevenue = Customer::sum('total_spent');
        $totalOrders = Customer::sum('total_orders');

        $newCustomersWeek = Customer::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_orders' => $totalOrders,
                'new_customers_week' => $newCustomersWeek,
                'documents_generated' => $totalOrders,
            ]
        ]);
    }

    public function getRevenueTrend(Request $request)
    {
        $filter = $request->query('filter', 'this_year');
        $data = [];

        if ($filter === 'this_month' || $filter === 'last_month') {
            $date = ($filter === 'this_month') ? Carbon::now() : Carbon::now()->subMonthNoOverflow();
            $daysInMonth = $date->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $data[$i] = ['label' => (string)$i, 'value' => 0];
            }

            $results = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->select(DB::raw('DAY(created_at) as day'), DB::raw('SUM(amount) as total'))
                ->groupBy('day')
                ->get();

            foreach ($results as $row) {
                $data[$row->day]['value'] = (float)$row->total;
            }
        } else {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $key => $monthName) {
                $data[$key + 1] = ['label' => $monthName, 'value' => 0];
            }

            $results = Order::whereYear('created_at', Carbon::now()->year)
                ->select(DB::raw('MONTH(created_at) as month_num'), DB::raw('SUM(amount) as total'))
                ->groupBy('month_num')
                ->get();

            foreach ($results as $row) {
                if (isset($data[$row->month_num])) {
                    $data[$row->month_num]['value'] = (float)$row->total;
                }
            }
        }

        return response()->json([
            'success' => true,
            'filter' => $filter,
            'data' => array_values($data)
        ]);
    }

    public function getRevenueByService()
    {
        $revenueByService = Order::join('services', 'orders.service_id', '=', 'services.id')
            ->select('services.title as service_name', DB::raw('SUM(orders.amount) as total_amount'))
            ->groupBy('services.id', 'services.title')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $revenueByService
        ]);
    }

    public function latestOrders()
    {
        try {
            $orders = Order::with('service:id,title','referralCode:id,code')
                ->latest()
                ->limit(10)
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $orders->map(function ($order) {
                $order->service_name = $order->service_name ?? ($order->service->title ?? 'N/A');

                $order->makeHidden(['customer_email', 'stripe_transaction_id', 'card_brand', 'card_brand', 'card_last4', 'document_status', 'created_at', 'updated_at']);

                $order->unsetRelation('service');

                return $order;
            });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
