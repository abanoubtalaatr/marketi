<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $stats = [
            'total_users' => User::where('role', UserRole::Customer)->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'recent_orders' => Order::with('user')->latest()->limit(10)->get(),
            'best_selling_products' => Product::orderByDesc('rating_count')->limit(5)->get(),
            'orders_by_status' => Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'monthly_revenue' => Payment::where('status', 'paid')
                ->where('created_at', '>=', now()->subMonths(6))
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('SUM(amount) as revenue')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];

        return $this->success($stats);
    }
}
