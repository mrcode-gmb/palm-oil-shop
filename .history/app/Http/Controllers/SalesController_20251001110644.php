<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Models\ProductAssignment;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        $query = Sale::with(['purchase.product', 'user', 'assignment']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->filled('payment_type') && $request->payment_type !== 'all') {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by salesperson (admin only)
        if (auth()->user()->isAdmin() && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // If salesperson, only show their sales
        if (auth()->user()->isSalesperson()) {
            $query->where('user_id', auth()->id());
        }

        // Get payment summary before pagination
        $paymentSummary = (clone $query)
            ->selectRaw('payment_type, count(*) as count, sum(total_amount) as total')
            ->groupBy('payment_type')
            ->get();

        $totalSales = (clone $query)->sum('total_amount');
        $totalProfit = (clone $query)->sum('profit');

        // Apply sorting and pagination
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $sales = $query->orderBy($sortBy, $sortDirection)->paginate(20);

        $salespeople = User::where('role', 'salesperson')->get();
        $paymentTypes = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'pos' => 'POS', 'mobile_money' => 'Mobile Money', 'credit' => 'Credit'];

        if ($request->has('export')) {
            return Excel::download(
                new SalesReportExport($sales->get()), 
                'sales-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('sales.index', compact(
            'sales',
            'salespeople',
            'paymentSummary',
            'totalSales',
            'totalProfit',
            'paymentTypes'
        ));
    }

    /**
     * Generate sales report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Generate sales report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        $query = Sale::with(['purchase.product', 'user']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->filled('payment_type') && $request->payment_type !== 'all') {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by salesperson (admin only)
        if (auth()->user()->isAdmin() && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif (auth()->user()->isSalesperson()) {
            $query->where('user_id', auth()->id());
        }

        // Get payment summary
        $paymentSummary = (clone $query)
            ->selectRaw('payment_type, count(*) as count, sum(total_amount) as total')
            ->groupBy('payment_type')
            ->get();

        $totalSales = (clone $query)->sum('total_amount');
        $totalProfit = (clone $query)->sum('profit');

        // Get all sales for the report
        $sales = $query->orderBy('sale_date', 'desc')->get();

        // If export is requested
        if ($request->has('export')) {
            return Excel::download(
                new SalesReportExport($sales, $request->all()), 
                'sales-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $salespeople = User::where('role', 'salesperson')->get();
        $paymentTypes = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'pos' => 'POS', 'mobile_money' => 'Mobile Money', 'credit' => 'Credit'];

        return view('sales.report', compact(
            'sales',
            'salespeople',
            'paymentSummary',
            'totalSales',
            'totalProfit',
            'paymentTypes'
        ));
    }

    /**
     * Export sales report to Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = Sale::with(['purchase.product', 'user']);

        // Apply the same filters as the report method
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }
        if ($request->filled('payment_type') && $request->payment_type !== 'all') {
            $query->where('payment_type', $request->payment_type);
        }
        if (auth()->user()->isAdmin() && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif (auth()->user()->isSalesperson()) {
            $query->where('user_id', auth()->id());
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        return Excel::download(
            new SalesReportExport($sales, $request->all()),
            'sales-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function mySale(Request $request)
    {
        $query = Sale::with(['purchase.product', 'user', 'assignment']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // If salesperson, only show their sales
        $query->where('user_id', auth()->id());

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        $salespeople = User::where('role', 'salesperson')->get();

        return view('sales.index', compact('sales', 'salespeople'));
    }


    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin can sell from any available inventory
            $products = Purchase::with("product")->where('quantity', '>', 0)->get();
            $assignments = collect(); // Empty collection for admin
        } else {
            // Staff can only sell from their assigned products
            $assignments = $user->activeAssignments()->with(['purchase.product'])->get();
            $products = collect(); // Empty collection for staff
        }
        
        return view('sales.create', compact('products', 'assignments'));
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:purchases,id',
            'assignment_id' => 'nullable|exists:product_assignments,id',
            'quantity' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'selling_price' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:cash,bank_transfer,pos,mobile_money,credit',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $assignment = null;
        
        // If staff member, validate they can sell this product
        if ($user->isSalesperson()) {
            if (!$request->assignment_id) {
                return back()->withErrors(['assignment_id' => 'Staff must select from assigned products.']);
            }
            
            $assignment = ProductAssignment::where('id', $request->assignment_id)
                ->where('user_id', $user->id)
                ->where('purchase_id', $request->product_id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->first();
                
            if (!$assignment) {
                return back()->withErrors(['assignment_id' => 'Invalid assignment or product not assigned to you.']);
            }
            
            // Check if enough assigned quantity is available
            if ($assignment->remaining_quantity < $request->quantity) {
                return back()->withErrors(['quantity' => 'Insufficient assigned quantity. Available: ' . $assignment->remaining_quantity]);
            }
        }

        $product = Purchase::with("product")->findOrFail($request->product_id);
        
        // For admin, check inventory stock
        if ($user->isAdmin() && $product->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        return $request->selling_price > 2000;
        return $product->purchase_price > $request->selling_price;

        // Check if selling price is reasonable
        if ($product->purchase_price < $request->selling_price) {
            return back()->withErrors(['selling_price' => 'Selling price cannot be less than the purchase price.']);
        }

        // return $request;

        // Calculate prices and profit

        $sellingPricePerUnit = $request->selling_price;

        $totalAmount = $sellingPricePerUnit * $request->quantity;

        $costPricePerUnit = $product->purchase_price;

        $totalCost = $costPricePerUnit * $request->quantity;

        $profit = $totalAmount - $totalCost;

        $seller_profit_per_unit = $product->seller_profit * $request->quantity;
        $net_profit_per_unit = $profit - $seller_profit_per_unit;


        // Create the sale
        $sale = Sale::create([
            'purchase_id' => $request->product_id,
            'user_id' => auth()->id(),
            'assignment_id' => $request->assignment_id,
            'quantity' => $request->quantity,
            'selling_price_per_unit' => $sellingPricePerUnit,
            'cost_price_per_unit' => $costPricePerUnit,
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'seller_profit_per_unit' => $seller_profit_per_unit,
            'net_profit_per_unit' => $net_profit_per_unit,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'payment_type' => $request->payment_type,
            'sale_status' => 'completed',
            'sale_date' => Carbon::today(),
            'notes' => $request->notes,
        ]);

        // Update assignment if this is a staff sale
        if ($assignment) {
            $assignment->increment('sold_quantity', $request->quantity);
            $assignment->increment('actual_total_sales', $totalAmount);
            
            // Update assignment status to in_progress if it was assigned
            if ($assignment->status === 'assigned') {
                $assignment->update(['status' => 'in_progress']);
            }
        } else {
            // Admin sale - update product stock directly
            $product->decrement('quantity', $request->quantity);
        }

        return redirect()->route('sales.my-sales')->with('success', 'Sale recorded successfully!');
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        
        // Check if user can view this sale
        if (auth()->user()->isSalesperson() && $sale->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $sale->load(['purchase.product', 'user', 'assignment']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit(Sale $sale)
    {
        // Only admin can edit sales
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit sales.');
        }
    }

    /**
     * Display the sales for the currently logged-in salesperson
     */
    public function mySales(Request $request)
    {
        // Ensure only salesperson can access this
        if (!auth()->user()->isSalesperson()) {
            abort(403, 'This action is unauthorized.');
        }

        $query = Sale::with(['purchase.product', 'assignment'])
            ->where('user_id', auth()->id())
            ->orderBy('sale_date', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->filled('payment_type') && $request->payment_type !== 'all') {
            $query->where('payment_type', $request->payment_type);
        }

        // Create a fresh query for payment summary to avoid any lingering order by clauses
        $paymentSummary = Sale::query()
            ->where('user_id', auth()->id())
            ->when($request->filled('start_date'), function($q) use ($request) {
                $q->whereDate('sale_date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                $q->whereDate('sale_date', '<=', $request->end_date);
            })
            ->when($request->filled('payment_type') && $request->payment_type !== 'all', function($q) use ($request) {
                $q->where('payment_type', $request->payment_type);
            })
            ->selectRaw('payment_type, count(*) as count, sum(total_amount) as total')
            ->groupBy('payment_type')
            ->get();

        // Get total sales
        $totalSales = $query->sum('total_amount');
        
        // Get paginated results with ordering
        $sales = $query->orderBy('sale_date', 'desc')->paginate(20);

        return view('sales.my-sales', [
            'sales' => $sales,
            'paymentSummary' => $paymentSummary,
            'totalSales' => $totalSales,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'payment_type' => $request->payment_type,
        ]);
    }

    /**
     * Update the specified sale
     */
    public function update(Request $request, Sale $sale)
    {
        // Only admin can update sales
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can update sales.');
        }

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $product = $sale->product;
        $oldQuantity = $sale->quantity;

        // Restore old stock
        $product->addStock($oldQuantity);

        // Check if enough stock is available for new quantity
        if ($product->current_stock < $request->quantity) {
            // Restore the stock we just added back
            $product->reduceStock($oldQuantity);
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        // Calculate new totals
        $sellingPricePerUnit = $product->selling_price;
        $totalAmount = $sellingPricePerUnit * $request->quantity;
        $costPricePerUnit = $product->getAverageCostPrice();
        $totalCost = $costPricePerUnit * $request->quantity;
        $profit = $totalAmount - $totalCost;

        // Update the sale
        $sale->update([
            'quantity' => $request->quantity,
            'selling_price_per_unit' => $sellingPricePerUnit,
            'cost_price_per_unit' => $costPricePerUnit,
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'notes' => $request->notes,
        ]);

        // Update product stock with new quantity
        $product->reduceStock($request->quantity);

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully!');
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Sale $sale)
    {
        // Only admin can delete sales
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete sales.');
        }

        // Restore stock
        $sale->purchase->addStock($sale->quantity);

        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
    }
}
