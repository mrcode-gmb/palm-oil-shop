<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    /**
     * Display Super Admin Dashboard
     */
    public function dashboard()
    {
        // Overall statistics across all businesses
        $stats = [
            'total_businesses' => Business::count(),
            'active_businesses' => Business::where('status', 'active')->count(),
            'total_users' => User::where('role', '!=', 'super_admin')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_salespeople' => User::where('role', 'salesperson')->count(),
            'total_products' => Product::count(),
            'total_sales_amount' => Sale::sum('total_amount'),
            'total_profit' => Sale::sum('profit'),
            'total_purchases' => Purchase::sum('total_cost'),
        ];

        // Recent businesses
        $recentBusinesses = Business::withCount(['users', 'products', 'sales'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Business performance (top 5 by sales)
        $topBusinesses = Business::withSum('sales', 'total_amount')
            ->withSum('sales', 'profit')
            ->orderBy('sales_sum_total_amount', 'desc')
            ->limit(5)
            ->get();

        // Monthly revenue trend (last 6 months)
        $monthlyRevenue = Sale::selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total_amount) as total, SUM(profit) as profit')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse();

        // Recent activities (last 10 sales across all businesses)
        $recentSales = Sale::with(['user', 'purchase.product', 'business'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('super-admin.dashboard', compact(
            'stats',
            'recentBusinesses',
            'topBusinesses',
            'monthlyRevenue',
            'recentSales'
        ));
    }

    /**
     * Display all users across all businesses
     */
    public function allUsers()
    {
        $users = User::with('business')
            ->where('role', '!=', 'super_admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.users.index', compact('users'));
    }

    /**
     * Display global reports
     */
    public function reports()
    {
        return view('super-admin.reports.index');
    }

    /**
     * Display system settings
     */
    public function settings()
    {
        return view('super-admin.settings.index');
    }
}
