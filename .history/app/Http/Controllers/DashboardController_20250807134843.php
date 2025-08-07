<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
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
        $lowStockProducts = Purchase::where('quantity', '<', 10)->get();
        
        // Sales statistics
        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $todayProfit = Sale::whereDate('sale_date', $today)->sum('profit');
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
            ->with('purchase')
            ->groupBy('purchase_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts', 'totalStock', 'lowStockProducts',
            'todaySales', 'todayProfit', 'monthlySales', 'monthlyProfit',
            'yearlySales', 'yearlyProfit', 'recentSales', 'recentPurchases',
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

        // Available products with stock
        $availableProducts = Purchase::withWhere('quantity', '>', 0)->get();

        return view('sales.dashboard', compact(
            'todaySales', 'todayProfit', 'monthlySales', 'monthlyProfit',
            'recentSales', 'availableProducts'
        ));
    }

    public function myStaff()
    {
        $users = User::where("role", 'salesperson')->orderBy('created_at', 'desc')->paginate(20);
        return view("users.index", compact('users'));
    }
}
