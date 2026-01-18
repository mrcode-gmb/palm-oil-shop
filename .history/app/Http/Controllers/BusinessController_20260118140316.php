<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BusinessController extends Controller
{
    /**
     * Display a listing of all businesses (Super Admin only)
     */
    public function index()
    {
        $businesses = Business::withCount(['users', 'products', 'sales', 'purchases'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('super-admin.businesses.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new business
     */
    public function create()
    {
        return view('super-admin.businesses.create');
    }

    /**
     * Store a newly created business
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            
            // Admin user details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        // Create the business
        $business = Business::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'business_type' => $validated['business_type'] ?? null,
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        // Create the admin user for this business
        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'status' => 'active',
            'business_id' => $business->id,
        ]);

        return redirect()->route('super-admin.businesses.index')
            ->with('success', 'Business created successfully with admin account!');
    }

    /**
     * Display the specified business
     */
    public function show(Business $business)
    {
        $business->load(['users', 'products', 'sales', 'purchases', 'expenses', 'wallet', 'businessCapital']);
        
        // Ensure wallet exists
        if (!$business->wallet) {
            $business->wallet()->create([
                'balance' => 0,
                'currency' => 'NGN',
                'status' => 'active'
            ]);
            $business->load('wallet'); // Reload the relationship
        }

        if (!$business->businessCapital) {
            $business->businessCapital()->create([
                'balance' => 0,
                'currency' => 'NGN',
                'status' => 'active'
            ]);
            $business->load('businessCapital'); // Reload the relationship
        }
        
        // Get statistics
        // Calculate sales from non-credit transactions
        $nonCreditSales = $business->sales()->where('payment_type', '!=', 'credit')->sum('total_amount');

        // Calculate total payments received from creditors
        $creditorPayments = $business->creditorTransactions()->where('type', 'credit')->sum('amount');

        $total_commission = $business->productAssignments()->sum('commission_amount');

        // Calculate the actual total sales
        $totalSales = $nonCreditSales + $creditorPayments;

        // Get statistics
        // Get all purchases to calculate inventory stats without pagination interference
        $allPurchases = $business->purchases()->get();

        $stats = [
            'total_users' => $business->users()->count(),
            'total_admins' => $business->users()->where('role', 'admin')->count(),
            'total_salespeople' => $business->users()->where('role', 'salesperson')->count(),
            'total_products' => $allPurchases->count(),
            'total_sales' => $totalSales,
            'total_profit' => $business->sales()->sum('profit'),
            'total_purchases' => $allPurchases->sum('total_cost'),
            'total_purchase_quantity' => $allPurchases->sum('quantity'), // This is historical total, not current stock
            'total_expenses' => $business->expenses()->sum('amount'),
            'current_inventory_value' => $allPurchases->sum(function($item) { 
                return $item->purchase_price * $item->quantity; 
            }),
            'current_stock_quantity' => $allPurchases->sum('quantity'),
        ];
        // Fetch transaction histories with pagination
        $sales = $business->sales()->with('user', 'purchase.product')->latest()->paginate(10, ['*'], 'sales');
        $purchases = $business->purchases()->with('product', 'user')->latest()->paginate(10, ['*'], 'purchases');
        $expenses = $business->expenses()->with('user')->latest()->paginate(10, ['*'], 'expenses');
        $creditorTransactions = $business->creditorTransactions()->with('creditor')->latest()->paginate(10, ['*'], 'creditor_transactions');

        $net_profit = $stats['total_profit'] - $stats['total_expenses'] - $total_commission;

        return view('super-admin.businesses.show', compact(
            'business',
            'stats',
            'sales',
            'purchases',
            'expenses',
            'creditorTransactions',
            'total_commission',
            'net_profit'
        ));
    }
    public function balanceWallet(Business $business)
    {
        $businessWalletBalance = $business->wallet;
        // return $business->sales;
        $totalSales = $business->sales->sum(callback: function($sale){
            return $sale->selling_price_per_unit * $sale->quantity;
        });

        $currentPurchaseInventory = $business->purchases->sum(function($purchases){
            return $purchases->purchase_price * $purchases->quantity;
        });

        $expenses = $business->expenses->sum("amount");

        return $business->productAssignments->sum(function($product){
                
                ($product->assigned_quantity - $product->sold_quantity - $product->returned_quantity) * ;
        });
        // return $business->sales->sum(function($sale){
        //     return $sale->seller_profit_per_unit * $sale->quantity;
        // });

    }
    /**
     * Show the form for editing the specified business
     */
    public function edit(Business $business)
    {
        return view('super-admin.businesses.edit', compact('business'));
    }

    /**
     * Update the specified business
     */
    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $business->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'business_type' => $validated['business_type'] ?? null,
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('super-admin.businesses.show', $business)
            ->with('success', 'Business updated successfully!');
    }

    /**
     * Remove the specified business
     */
    public function destroy(Business $business)
    {
        $businessName = $business->name;
        $business->delete();

        return redirect()->route('super-admin.businesses.index')
            ->with('success', "Business '{$businessName}' deleted successfully!");
    }

    /**
     * Toggle business status
     */
    public function toggleStatus(Business $business)
    {
        $newStatus = $business->status === 'active' ? 'inactive' : 'active';
        $business->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Business status updated to {$newStatus}!");
    }

    /**
     * Show business users
     */
    public function users(Business $business)
    {
        $users = $business->users()->paginate(15);
        
        return view('super-admin.businesses.users', compact('business', 'users'));
    }

    /**
     * Show business analytics
     */
    public function analytics(Business $business)
    {
        // Monthly sales data for the last 12 months
        $monthlySales = $business->sales()
            ->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total_amount) as total, SUM(profit) as profit')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Top selling products
        $topProducts = $business->sales()
            ->selectRaw('purchase_id, SUM(quantity) as total_quantity, SUM(total_amount) as total_sales')
            ->groupBy('purchase_id')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->with('purchase.product')
            ->get();

        // Top performing salespeople
        $topSalespeople = $business->sales()
            ->selectRaw('user_id, COUNT(*) as sales_count, SUM(total_amount) as total_sales, SUM(profit) as total_profit')
            ->groupBy('user_id')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->with('user')
            ->get();

        return view('super-admin.businesses.analytics', compact('business', 'monthlySales', 'topProducts', 'topSalespeople'));
    }
}
