<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;

class InventoryController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Purchase::query()->with('product');

        // Filter by unit type
        if ($request->filled('unit_type')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('unit_type', $request->unit_type);
            });
        }

        // Filter by stock level
        if ($request->filled('stock_filter')) {
            switch ($request->stock_filter) {
                case 'low':
                    $query->where('quantity', '<', 10);
                    break;
                case 'out':
                    $query->where('quantity', '=', 0);
                    break;
                case 'available':
                    $query->where('quantity', '>', 0);
                    break;
            }
        }

        $products = $query->orderBy('id')->paginate(20);

        return view('inventory.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $products = Product::all();
        return view('inventory.create', ['products' => $products]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|in:Customize,Uncustomize',
            'description' => 'nullable|string',
        ]);

        Product::create([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'current_stock' => 0,
            'description' => $request->description,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show($productId)
    {
        // Fetch single product by ID
        $product = Purchase::with(['product', 'sales'])->findOrFail($productId);
        // return $product;
        // Initialize stock movements collection
        $stockMovements = collect();

        // Add purchases as positive movements
        // Add purchase as a positive movement
        $stockMovements->push([
            'type' => 'purchase',
            'date' => $product->purchase_date ?? null,
            'quantity' => $product->quantity ?? null,
            'description' => "Purchase from {$product->supplier_name}",
            'user' => $product->user->name ?? null,
            'created_at' => $product->created_at ?? null,
        ]);


        // Add sales as negative movements
        foreach ($product->sales as $sale) {
            $stockMovements->push([
                'type' => 'sale',
                'date' => $sale->sale_date,
                'quantity' => -$sale->quantity,
                'description' => "Sale to " . ($sale->customer_name ?: 'Customer'),
                'user' => $sale->user->name,
                'created_at' => $sale->created_at,
            ]);
        }

        // Sort by newest first
        $stockMovements = $stockMovements->sortByDesc('created_at');

        return view('inventory.show', compact('product', 'stockMovements'));
    }


    /**
     * Show the form for editing the specified product
     */
    public function edit($productId)
    {
        $product = Product::with(['purchases.user', 'sales.user'])->findOrFail($productId);
        return view('inventory.edit', compact('product'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|in:litre,jerrycan',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'selling_price' => $request->selling_price,
            'description' => $request->description,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Check if product has any sales or purchases
        if ($product->sales()->count() > 0 || $product->purchases()->count() > 0) {
            return redirect()->route('inventory.index')
                ->withErrors(['error' => 'Cannot delete product with existing sales or purchases.']);
        }

        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Adjust stock manually (admin only)
     */
    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'adjustment' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        $oldStock = $product->current_stock;
        $newStock = $oldStock + $request->adjustment;

        if ($newStock < 0) {
            return back()->withErrors(['adjustment' => 'Stock adjustment would result in negative stock.']);
        }

        $product->update(['current_stock' => $newStock]);

        // Log the adjustment (you could create a separate model for this)
        // For now, we'll just redirect with success message

        return redirect()->route('inventory.show', $product)
            ->with('success', "Stock adjusted from {$oldStock} to {$newStock}. Reason: {$request->reason}");
    }
}
