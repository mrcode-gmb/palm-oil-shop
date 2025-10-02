<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ProductAssignment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Get statistics
        $totalProducts = Product::count();
        $totalStock = Purchase::sum('quantity');
        $lowStockProducts = Purchase::with("product")->where('quantity', '<', 10)->get();

        $totalStaffs = User::where("role", 'salesperson')->get();

        // Sales statistics
        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $todayProfit = Sale::whereDate('sale_date', $today)->sum('profit');
        $todaySellerProfitPerUnit = Sale::whereDate('sale_date', $today)->sum('seller_profit_per_unit');
        $todayExpenses = Expenses::whereDate('created_at', $today)->sum('amount');
        $monthlySales = Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount');
        $monthlyProfit = Sale::where('sale_date', '>=', $thisMonth)->sum('profit');
        $yearlySales = Sale::where('sale_date', '>=', $thisYear)->sum('total_amount');
        $yearlyProfit = Sale::where('sale_date', '>=', $thisYear)->sum('profit');

        // Recent activities
        $recentSales = Sale::with(['purchase.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPurchases = Purchase::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top selling products
        $topProducts = Sale::selectRaw('purchase_id, SUM(quantity) as total_sold, SUM(total_amount) as total_revenue')
            ->with('purchase.product')
            ->groupBy('purchase_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // return $lowStockProducts;
        return view('admin.dashboard', compact(
            'totalProducts',
            'totalStock',
            'todaySellerProfitPerUnit',
            'todayExpenses',
            'lowStockProducts',
            'todaySales',
            'todayProfit',
            'monthlySales',
            'monthlyProfit',
            'totalStaffs',
            'yearlySales',
            'yearlyProfit',
            'recentSales',
            'recentPurchases',
            'topProducts'
        ));
    }

    /**
     * Sales Dashboard
     */
    public function salesDashboard()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get user's sales statistics
        $todaySales = Sale::where('user_id', $user->id)
            ->whereDate('sale_date', $today)
            ->sum('total_amount');

        $todayProfit = Sale::where('user_id', $user->id)
            ->whereDate('sale_date', $today)
            ->sum('profit');

        $monthlySales = Sale::where('user_id', $user->id)
            ->where('sale_date', '>=', $thisMonth)
            ->sum('total_amount');

        $monthlyProfit = Sale::where('user_id', $user->id)
            ->where('sale_date', '>=', $thisMonth)
            ->sum('profit');

        // Recent sales by this user
        $recentSales = Sale::with(['purchase.product', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get user's product assignments with metrics
        $assignments = ProductAssignment::with(['purchase.product'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->get()
            ->map(function($assignment) {
                $assignment->sold_percentage = $assignment->assigned_quantity > 0 
                    ? ($assignment->sold_quantity / $assignment->assigned_quantity) * 100 
                    : 0;
                $assignment->remaining_quantity = $assignment->assigned_quantity - $assignment->sold_quantity;
                return $assignment;
            });

        return $assignments->sum("assigned_quantity");
        // Available products with stock
        $availableProducts = Purchase::with(['product', 'user', 'sales'])
            ->where('quantity', '>', 0)
            ->get();

        return view('sales.dashboard', compact(
            'todaySales',
            'todayProfit',
            'monthlySales',
            'monthlyProfit',
            'recentSales',
            'availableProducts',
            'totalStock',
            'assignments'
        ));
    }

    public function myStaff()
    {
        $users = User::where("role", 'salesperson')->orderBy('created_at', 'desc')->paginate(20);
        return view("users.index", compact('users'));
    }
}
