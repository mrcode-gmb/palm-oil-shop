<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {

        $product = Product::findOrFail(5);

        // Check if enough stock is available
        if ($product->current_stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        // Calculate prices and profit
        $sellingPricePerUnit = $product->selling_price;
        $totalAmount = $sellingPricePerUnit * $request->quantity;
        $costPricePerUnit = $product->getAverageCostPrice();
        $totalCost = $costPricePerUnit * $request->quantity;
        $profit = $totalAmount - $totalCost;

        return $
        $query = Sale::with(['product', 'user']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by salesperson (admin only)
        if (auth()->user()->isAdmin() && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // If salesperson, only show their sales
        if (auth()->user()->isSalesperson()) {
            $query->where('user_id', auth()->id());
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        $salespeople = User::where('role', 'salesperson')->get();

        return view('sales.index', compact('sales', 'salespeople'));
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        $products = Product::where('current_stock', '>', 0)->get();
        return view('sales.create', compact('products'));
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if enough stock is available
        if ($product->current_stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        // Calculate prices and profit
        $sellingPricePerUnit = $product->selling_price;
        $totalAmount = $sellingPricePerUnit * $request->quantity;
        $costPricePerUnit = $product->getAverageCostPrice();
        $totalCost = $costPricePerUnit * $request->quantity;
        $profit = $totalAmount - $totalCost;

        // Create the sale
        $sale = Sale::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'quantity' => $request->quantity,
            'selling_price_per_unit' => $sellingPricePerUnit,
            'cost_price_per_unit' => $costPricePerUnit,
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'sale_date' => Carbon::today(),
            'notes' => $request->notes,
        ]);

        // Update product stock
        $product->reduceStock($request->quantity);

        return redirect()->route('sales.index')->with('success', 'Sale recorded successfully!');
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

        $sale->load(['product', 'user']);
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

        $products = Product::all();
        $sale->load(['product']);
        return view('sales.edit', compact('sale', 'products'));
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
        $sale->product->addStock($sale->quantity);

        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
    }
}
