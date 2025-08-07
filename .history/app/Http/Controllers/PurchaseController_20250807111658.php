<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Product;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['product', 'user']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $purchases = $query->orderBy('created_at', 'desc')->paginate(20);
        $products = Product::all();

        return view('purchases.index', compact('purchases', 'products'));
    }

    /**
     * Show the form for creating a new purchase
     */
    public function create()
    {
        $products = Product::all();
        return view('purchases.create', compact('products'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        // return $request;
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0.01',
            'buying_price_per_unit' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'selling_profit_per_unit' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $totalCost = $request->quantity * $request->buying_price_per_unit;

        // Create the purchase
        $purchase = Purchase::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'supplier_name' => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'quantity' => $request->quantity,
            'purchase_price' => $request->buying_price_per_unit,
            'total_cost' => $totalCost,
            'selling_price' => $request->selling_price,
            'seller_profit' => $request->selling_profit_per_unit,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,

        ]);

        // Update product stock
        // $product = Product::findOrFail($request->product_id);
        // $product->addStock($request->quantity);

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully!');
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['product', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase
     */
    public function edit(Purchase $purchase)
    {
        $products = Product::all();
        $purchase->load(['product']);
        return view('purchases.edit', compact('purchase', 'products'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0.01',
            'buying_price_per_unit' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $product = $purchase->product;
        $oldQuantity = $purchase->quantity;

        // Remove old stock quantity
        $product->reduceStock($oldQuantity);

        $totalCost = $request->quantity * $request->buying_price_per_unit;

        // Update the purchase
        $purchase->update([
            'supplier_name' => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'quantity' => $request->quantity,
            'buying_price_per_unit' => $request->buying_price_per_unit,
            'total_cost' => $totalCost,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
        ]);

        // Add new stock quantity
        $product->addStock($request->quantity);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(Purchase $purchase)
    {
        // Remove stock from product
        $purchase->product->reduceStock($purchase->quantity);

        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
    }
}
