<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseHistory;
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
            'total_sales' => $business->sales->sum(function($sale){
                return $sale->selling_price_per_unit * $sale->quantity;
            }),
            'total_profit' => $business->sales->sum('profit'),
            'total_quantity_sold' => $business->sales->sum('quantity'),
            'total_purchases' => $business->purchaseHistory->sum('total_cost'),
            'total_purchase_quantity' => $business->purchaseHistory->sum('quantity'), // This is historical total, not current stock
            'total_expenses' => $business->expenses()->sum('amount'),
            'current_inventory_value' => $allPurchases->sum(function ($item) {
                return $item->purchase_price * $item->quantity;
            }),
            'current_stock_quantity' => $allPurchases->sum('quantity'),
        ];

        // Fetch transaction histories with pagination
        $sales = $business->sales()->with('user', 'purchase.product')->latest()->paginate(10, ['*'], 'sales');
        $purchases = $business->purchases()->with('product', 'user')->latest()->paginate(10, ['*'], 'purchases');
        $expenses = $business->expenses()->with('user')->latest()->paginate(10, ['*'], 'expenses');
        $creditorTransactions = $business->creditorTransactions()->with('creditor')->latest()->paginate(10, ['*'], 'creditor_transactions');
        
        // Calculate cost of remaining products in assignments (unsold inventory with staff)
        // Use the model's remaining_quantity attribute which correctly calculates: assigned - sold - collected
        $productAssignmentCost = $business->productAssignments->sum(function ($assignment) {
            return ($assignment->sold_quantity) * $assignment->purchase->purchase_price;
        });
        // return $productAssignmentCost;
        $productAssignmentQuantity = $business->productAssignments->sum(function ($assignment) {
            return $assignment->assigned_quantity - $assignment->sold_quantity - $assignment->returned_quantity;
        });
        
        // Calculate cost of inventory in warehouse (actual remaining stock in purchases table)
        // purchases.quantity shows actual warehouse stock (reduced when products are assigned/sold)
        $warehouseInventoryCost = $business->purchases->sum(function ($purchases) {
            return $purchases->quantity * $purchases->purchase_price;
        });
        
        // Total inventory cost = warehouse stock + assigned stock (both are separate physical locations)
        // Warehouse: What's physically in the warehouse (purchases.quantity)
        // Assigned: What's physically with staff (assigned - sold - returned)
        return [number_format($productAssignmentCost), number_format($business->sales->sum(function($sale){
            return $sale->purchase->purchase_price * $sale->quantity;
        }))];
        $totalInventoryCost = $warehouseInventoryCost + $productAssignmentCost + $business->sales->sum('total_amount');
        return number_format($totalInventoryCost, 2);
        // Calculate net profit with detailed breakdown
        $totalSalesProfit = $stats['total_profit'];
        $totalExpenses = $stats['total_expenses'];
        $totalCommission = $total_commission;
        
        $net_profit = $totalSalesProfit - $totalExpenses - $totalCommission;

        // Money owed to the business by creditors (receivables)
        $totalCreditorBalance = $business->creditors->sum("balance");

        // Actual Profit = Net Profit (operating profit from sales)
        // This is the real profit earned: Sales Profit - Expenses - Commission
        $actualProfit = $net_profit;
        
        // Debug information for discrepancy checking
        $profitBreakdown = [
            'sales_profit' => $totalSalesProfit,
            'expenses' => $totalExpenses,
            'commission' => $totalCommission,
            'net_profit' => $net_profit,
        ];
        
        // Detailed diagnostic data for checking database inconsistencies
        $diagnostics = [
            // Product Assignments Check
            'total_assignments' => $business->productAssignments->count(),
            'assignments_with_commission' => $business->productAssignments->where('commission_amount', '>', 0)->count(),
            'total_commission_from_db' => $business->productAssignments->sum('commission_amount'),
            'commission_used_in_calc' => $total_commission,
            
            // Purchases Check
            'total_purchase_records' => $business->purchases->count(),
            'warehouse_qty' => $business->purchases->sum('quantity'),
            'warehouse_cost' => $warehouseInventoryCost,
            
            // Assignment Inventory Check
            'assigned_qty' => $productAssignmentQuantity,
            'assigned_cost' => $productAssignmentCost,
            
            // Sales Check
            'total_sales_count' => $business->sales->count(),
            'sales_profit_sum' => $business->sales->sum('profit'),
            'sales_profit_in_stats' => $stats['total_profit'],
            
            // Expenses Check
            'expenses_count' => $business->expenses->count(),
            'expenses_sum' => $business->expenses->sum('amount'),
            'expenses_in_stats' => $stats['total_expenses'],
        ];
        
        // Actual Wallet Balance = Cash + Receivables + Inventory Value
        // This shows total business value (liquid + non-liquid assets)
        $actualWalletBalance = $business->wallet->balance + $totalCreditorBalance + $totalInventoryCost;
        // return number_format($actualWalletBalance, 2);

        return view('super-admin.businesses.show', compact(
            'business',
            'stats',
            'sales',
            'purchases',
            'expenses',
            'creditorTransactions',
            'total_commission',
            'net_profit',
            'productAssignmentCost',
            'productAssignmentQuantity',
            'warehouseInventoryCost',
            'totalInventoryCost',
            'totalCreditorBalance',
            'actualWalletBalance',
            'actualProfit',
            'profitBreakdown',
            'diagnostics',
        ));
    }
    public function balanceWallet(Business $business)
    {
        $businessWalletBalance = $business->wallet;

        $totalSales = $business->sales->sum(callback: function ($sale) {
            return $sale->selling_price_per_unit * $sale->quantity;
        });

        $currentPurchaseInventory = $business->purchases->sum(function ($purchases) {
            return $purchases->quantity * $purchases->purchase_price;
        });


        $historyPurchaseInventory = $business->purchaseHistory->sum(function ($purchases) {
            return $purchases->purchase_price * $purchases->quantity;
        });

        $expenses = $business->expenses->sum("amount");

        $productAssignment = $business->productAssignments->where("status", "!=", "completed")->sum(function ($assignment) {
            $remainingQuantity =
                $assignment->assigned_quantity
                - $assignment->sold_quantity
                - $assignment->returned_quantity;

            $purchasePrice = $assignment->purchase->purchase_price ?? 0;

            return $remainingQuantity * $purchasePrice;
        });

        $totalCreditorBalance =  $business->creditors->sum("balance");

        $totalCreditorPaid =  $business->creditorTransactions->where("type", "credit")->sum("amount");

        $balance = ($businessWalletBalance->balance ?? 0)
        //  + ($totalSales ?? 0)
         + ($currentPurchaseInventory ?? 0)
        //  - ($historyPurchaseInventory ?? 0)
        //  - ($expenses ?? 0)
         + ($productAssignment ?? 0)
         + ($totalCreditorBalance ?? 0);
        //  + ($totalCreditorPaid ?? 0);
        $netProfit = $balance - $businessWalletBalance->balance;

        $actualWalletBalance =
            $balance
            + $totalCreditorBalance
            + $productAssignment
            + $currentPurchaseInventory;
        // - $expenses;
        // - $totalCreditorBalance;
        // return $actualWalletBalance;
        $actualWalletBalance =
            ($businessWalletBalance->balance ?? 0)
            + ($totalSales ?? 0)
            + ($totalCreditorPaid ?? 0)
            - ($historyPurchaseInventory ?? 0)
            - ($expenses ?? 0);

        return $balance;

        // return $business->sales->sum(function($sale){
        //     return $sale->seller_profit_per_unit * $sale->quantity;
        // });

    }

    
    private function createPurchaseHistory(Business $business)
    {
        $actualPurchase = $business->purchases->map(function($purchase){
            $inventoryRemain = (int) $purchase->quantity;
            $assignedInventory = $purchase->assignProduct->sum(function($assignment){
                return (int)$assignment->assigned_quantity - (int)$assignment->returned_quantity;
            });
            $actualPurchaseQuantity = $inventoryRemain + $assignedInventory;
            $purchase->quantity = $actualPurchaseQuantity;

            return $purchase;
        });

        foreach ($actualPurchase as $purchase) {
            // PurchaseHistory::create([
            //     'business_id' => $purchase->business_id,
            //     'product_id' => $purchase->product_id,
            //     'user_id' => $purchase->user_id,
            //     'supplier_name' => $purchase->supplier_name,
            //     'supplier_phone' => $purchase->supplier_phone,
            //     'quantity' => $purchase->quantity,
            //     'purchase_price' => $purchase->purchase_price,
            //     "total_cost" => $purchase->total_cost,
            //     'selling_price' => $purchase->selling_price,
            //     'seller_profit' => $purchase->seller_profit,
            //     'purchase_date' => $purchase->purchase_date,
            //     'notes' => $purchase->notes,
            // ]);
        }
        return $actualPurchase;
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
