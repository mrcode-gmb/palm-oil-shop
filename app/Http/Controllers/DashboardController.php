<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ProductAssignment;
use App\Models\CreditorTransaction;
use App\Traits\BusinessScoped;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use BusinessScoped;
    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Get statistics - scoped to business
        $totalProducts = $this->scopeToCurrentBusiness(Product::class)->count();
        $totalStock = $this->scopeToCurrentBusiness(Purchase::class)->sum('quantity');
        $totalcostInventory = $this->scopeToCurrentBusiness(Purchase::class)->get()->sum(function($purchase){
            return $purchase->quantity * $purchase->purchase_price;
        });
        $lowStockProducts = $this->scopeToCurrentBusiness(Purchase::class)
            ->with("product")
            ->where('quantity', '<', 10)
            ->get();

        $totalStaffs = $this->scopeToCurrentBusiness(User::class)
            ->where("role", 'salesperson')
            ->get();

        // Sales statistics - scoped to business
        $todayCashSales = $this->scopeToCurrentBusiness(Sale::class)
            ->whereDate('sale_date', $today)
            ->where('payment_type', '!=', 'credit')
            ->sum('total_amount');

        $todayCreditSales = $this->scopeToCurrentBusiness(Sale::class)
            ->whereDate('sale_date', $today)
            ->where('payment_type', 'credit')
            ->sum('total_amount');

        $todayCreditorPayments = CreditorTransaction::join('creditors', 'creditor_transactions.creditor_id', '=', 'creditors.id')
            ->where('creditors.business_id', $this->getBusinessId())
            ->whereDate('creditor_transactions.created_at', $today)
            ->sum('creditor_transactions.amount');
        $todaySales = $todayCashSales + $todayCreditorPayments;
        $todayProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->whereDate('sale_date', $today)
            ->sum('profit');
        $todaySellerProfitPerUnit = $this->scopeToCurrentBusiness(Sale::class)
            ->whereDate('sale_date', $today)
            ->sum('seller_profit_per_unit');
        $todayExpenses = $this->scopeToCurrentBusiness(Expenses::class)
            ->whereDate('created_at', $today)
            ->sum('amount');
        $monthlySales = $this->scopeToCurrentBusiness(Sale::class)
            ->where('sale_date', '>=', $thisMonth)
            ->sum('total_amount');
        $monthlyProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->where('sale_date', '>=', $thisMonth)
            ->sum('profit');
        $yearlySales = $this->scopeToCurrentBusiness(Sale::class)
            ->where('sale_date', '>=', $thisYear)
            ->sum('total_amount');
        $yearlyProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->where('sale_date', '>=', $thisYear)
            ->sum('profit');

        // Recent activities - scoped to business
        $recentSales = $this->scopeToCurrentBusiness(Sale::class)
            ->with(['purchase.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPurchases = $this->scopeToCurrentBusiness(Purchase::class)
            ->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top selling products - scoped to business
        $topProducts = $this->scopeToCurrentBusiness(Sale::class)
            ->selectRaw('purchase_id, SUM(quantity) as total_sold, SUM(total_amount) as total_revenue')
            ->with('purchase.product')
            ->groupBy('purchase_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // return $lowStockProducts;
        return view('admin.dashboard', compact(
            'totalProducts',
            'totalStock',
            'totalcostInventory',
            'todaySellerProfitPerUnit',
            'todayExpenses',
            'lowStockProducts',
            'todaySales',
            'todayCreditSales',
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

        // Get user's sales statistics - scoped to business
        $todayCashSales = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->whereDate('sale_date', $today)
            ->where('payment_type', '!=', 'credit')
            ->sum('total_amount');
        $todayCashSalescredit = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->whereDate('sale_date', $today)
            ->where('payment_type', '=', 'credit')
            ->sum('amount_paid');
        $todayCreditorPayments = CreditorTransaction::join('creditors', 'creditor_transactions.creditor_id', '=', 'creditors.id')
            ->where('creditors.business_id', $this->getBusinessId())
            ->where('creditors.user_id', $user->id)
            ->whereDate('creditor_transactions.created_at', $today)
            ->where('creditor_transactions.type', 'credit')
            ->sum('creditor_transactions.amount');

        $todaySales = $todayCashSales + $todayCreditorPayments + $todayCashSalescredit;

        $todayProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->whereDate('sale_date', $today)
            ->sum('profit');

        $monthlyCashSales = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->where('sale_date', '>=', $thisMonth)
            ->where('payment_type', '!=', 'credit')
            ->sum('total_amount');

        $monthlyCreditorPayments = CreditorTransaction::join('creditors', 'creditor_transactions.creditor_id', '=', 'creditors.id')
            ->where('creditors.business_id', $this->getBusinessId())
            ->where('creditors.user_id', $user->id)
            ->whereDate('creditor_transactions.created_at', '>=', $thisMonth)
            ->where('creditor_transactions.type', 'credit')
            ->sum('creditor_transactions.amount');
        
        $monthlySales = $monthlyCashSales + $monthlyCreditorPayments;

        $monthlyProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->where('sale_date', '>=', $thisMonth)
            ->sum('profit');

        $totalCreditSales = $this->scopeToCurrentBusiness(Sale::class)
            ->where('user_id', $user->id)
            ->where('payment_type', 'credit')
            ->sum('total_amount');

        // Recent sales by this user - scoped to business
        $recentSales = $this->scopeToCurrentBusiness(Sale::class)
            ->with(['purchase.product', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get user's product assignments with metrics - scoped to business
        $assignments = $this->scopeToCurrentBusiness(ProductAssignment::class)
            ->with(['purchase.product'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->get()
            ->map(function ($assignment) {
                $assignment->sold_percentage = $assignment->assigned_quantity > 0
                    ? ($assignment->sold_quantity / $assignment->assigned_quantity) * 100
                    : 0;
                $assignment->remaining_quantity = $assignment->assigned_quantity - $assignment->sold_quantity;
                return $assignment;
            });

        $totalStock = $assignments->sum("assigned_quantity");
        $totalSoldQuantity = $assignments->sum("sold_quantity");
        $commission_rate = $assignments->sum("commission_rate");
        $totalRemainingQuantityStock = $assignments->sum("assigned_quantity") - $assignments->sum("sold_quantity");
        
        // Available products with stockProductAssigned - scoped to business
        $availableProducts = $this->scopeToCurrentBusiness(ProductAssignment::class)
            ->where("user_id", auth()->id())
            ->with(['purchase.product', 'user', 'sales'])
            ->where('assigned_quantity', '>', 0)
            ->orderByDesc("id")
            ->limit(10)
            ->get();

        return view('sales.dashboard', compact(
            'todaySales',
            'todayProfit',
            'monthlySales',
            'monthlyProfit',
            'totalCreditSales',
            'recentSales',
            'availableProducts',
            'totalStock',
            'assignments',
            "totalSoldQuantity",
            "commission_rate",
            "totalRemainingQuantityStock",
        ));
    }

    public function myStaff()
    {
        // Only show salespersons from the same business
        $users = $this->scopeToCurrentBusiness(User::class)
            ->where("role", 'salesperson')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view("users.index", compact('users'));
    }
}
