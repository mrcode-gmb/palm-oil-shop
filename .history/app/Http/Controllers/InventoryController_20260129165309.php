<?php

namespace App\Http\Controllers;

use App\Models\AdjustmentProduct;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ProductAssignment;
use App\Traits\BusinessScoped;

class InventoryController extends Controller
{
    use BusinessScoped;
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin sees inventory from their business only
            $query = $this->scopeToCurrentBusiness(Purchase::class)->with('product')->where("quan");

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

            $products = $query->orderBy('id')->get();
            $assignments = collect(); // Empty for admin
            
        } else {
            // Staff sees their assigned products from their business
            $query = $this->scopeToCurrentBusiness(ProductAssignment::class)
                ->with(['purchase.product', 'user'])
                ->where('user_id', $user->id);

            // Filter by status
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            } else {
                // Default to active assignments
                $query->whereIn('status', ['assigned', 'in_progress']);
            }

            $assignments = $query->orderBy('created_at', 'desc')->get();
            $products = collect(); // Empty for staff
        }



        return view('inventory.index', compact('products', 'assignments'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $products = $this->scopeToCurrentBusiness(Product::class)->get();
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
            'low_stock' => "required",
        ]);

        $data = $this->addBusinessId([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'current_stock' => 0,
            'low_stock'=> $request->low_stock,
            'low_stock_threshold'=> $request->low_stock,
            'description' => $request->description,
        ]);

        Product::create($data);

        return redirect()->route('inventory.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show($productId)
    {
        // Fetch single product by ID - ensure it belongs to current business
        $product = $this->scopeToCurrentBusiness(Purchase::class)
            ->with(['product', 'sales'])
            ->findOrFail($productId);

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
        $adjustmentRecord = AdjustmentProduct::with("purchase")->where("purchase_id", $productId)->get();
        // Sort by newest first
        $stockMovements = $stockMovements->sortByDesc('created_at');

        return view('inventory.show', compact('product', 'stockMovements', 'adjustmentRecord'));
    }


    /**
     * Show the form for editing the specified product
     */
    public function edit($productId)
    {
        $product = Product::with("purchase.sales")->findOrFail($productId);
        // return $product;
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
            'unit_type' => 'required|in:Customize,Uncustomize',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'description' => $request->description,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::with("purchase")->findOrFail($id);
        // Check if product has any sales or purchases
        if ($product->purchase()->count() > 0) {
            return redirect()->route('inventory.index')
                ->withErrors(['error' => 'Cannot delete product with existing sales or purchases.']);
        }

        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Product deleted successfully!');
    }

    public function adjustStockDelete(AdjustmentProduct $adjustment)
    {
        // Check if product has any sales or purchases
        if ($adjustment->purchase->quantity < $adjustment->adjustment) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete adjustment history while don`t have value in purchase.']);
        }
        $oldStock = $adjustment->purchase->quantity;
        $newStock = $oldStock - $adjustment->adjustment;

        // return $newStock;

        $adjustment->purchase->update(['quantity' => $newStock]);
        $adjustment->delete();

        return redirect()->back()->with('success', 'Adjustment deleted successfully!');
        // return $adjustment;
    }
    /**
     * Adjust stock manually (admin only)
     */
    public function adjustStock(Request $request, Purchase $product)
    {
        $request->validate([
            'adjustment' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        // return $product;
        $oldStock = $product->quantity;
        $newStock = $oldStock + $request->adjustment;

        AdjustmentProduct::create([
            "purchase_id" => $product->id,
            "adjustment" => $request->adjustment,
            "reason" => $request->reason,
        ]);

        if ($newStock < 0) {
            return back()->withErrors(['adjustment' => 'Stock adjustment would result in negative stock.']);
        }

        $product->update(['quantity' => $newStock]);

        // Log the adjustment (you could create a separate model for this)
        // For now, we'll just redirect with success message

        return redirect()->route('inventory.show', $product)
            ->with('success', "Stock adjusted from {$oldStock} to {$newStock}. Reason: {$request->reason}");
    }
}
