<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseHistory;
use App\Models\Product;
use App\Traits\BusinessScoped;
use App\Models\ProductAssignment;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    use BusinessScoped;
    
    /**
     * Display a listing of purchases
     */

    private function getPurchase($created_at, $user_id)
    {
        return Purchase::with(['product', 'user'])->where("created_at", $created_at)->where("user_id", $user_id)->first();
    }
    public function index(Request $request)
    {
        $query = $this->scopeToCurrentBusiness(PurchaseHistory::class)->with(['product', 'user']);

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

        $purchases = $query->orderBy('created_at', 'desc')->get();
        $products = $this->scopeToCurrentBusiness(Product::class)->get();

        // return $purchases; 
        return view('purchases.index', compact('purchases', 'products'));
    }

    /**
     * Show the form for creating a new purchase
     */
    public function create()
    {
        $products = $this->scopeToCurrentBusiness(Product::class)->get();
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
            // 'selling_price' => 'required|numeric|min:0',
            'selling_profit_per_unit' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        $totalCost = $request->quantity * $request->buying_price_per_unit;
        
        
        
        // Create the purchase with business_id
        $data = $this->addBusinessId([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'supplier_name' => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'quantity' => $request->quantity,
            'purchase_price' => $request->buying_price_per_unit,
            'total_cost' => $totalCost,
            'selling_price' => 0,
            'seller_profit' => $request->selling_profit_per_unit,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
        ]);
        
        $purchase = Purchase::create($data);
        $purchaseHistories = PurchaseHistory::create($data);

        // Update product stock
        $product = Product::findOrFail($request->product_id);
        $product->addStock($request->quantity);

        $businessWallet = auth()->user()->business->wallet;
        $businessWallet->balance -= $totalCost;
        $businessWallet->save();

        $transaction = $businessWallet->transactions()->create([
            'wallet_id' => $businessWallet->id,
            'business_id'=> auth()->user()->business->id,
            'amount' => $totalCost,
            'type' => "debit",
            'reference' => 'PURCHASE-' . strtoupper(\Str::random(10)),
            'description' => "Purchase product history",
            'status' => 'completed',
            'metadata' => $data
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully!');
    }

    /**
     * Display the specified purchase
     */
    public function show(PurchaseHistory $purchase)
    {
        $purchaseReal = $this->getPurchase($purchase->created_at, $purchase->user_id);
        $purchase->load(['product', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase
     */
    public function edit(PurchaseHistory $purchase)
    {

        $products = Product::all();
        $purchase->load(['product']);
        $purchaseReal = $this->getPurchase($purchase->created_at, $purchase->user_id);
        return view('purchases.edit', compact('purchase', 'products'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, PurchaseHistory $purchase)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0.01',
            'cost_price_per_unit' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseReal = $this->getPurchase($purchase->created_at, $purchase->user_id);
        $product = $purchase->product;
        $oldQuantity = $purchase->quantity;

        // Remove old stock quantity
        $product->reduceStock($oldQuantity);
        // $purchase->reduceStock($request->quantity);
        // $purchaseReal->reduceStock($request->quantity);

        $totalCost = $request->quantity * $request->cost_price_per_unit;

        // Update the purchase
        $purchase->update([
            'supplier_name' => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'quantity' => $request->quantity,
            'purchase_price' => $request->cost_price_per_unit,
            'total_cost' => $totalCost,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
        ]);
        $purchaseReal->update([
            'supplier_name' => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'quantity' => $request->quantity,
            'purchase_price' => $request->cost_price_per_unit,
            'total_cost' => $totalCost,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
        ]);

        // Add new stock quantity
        $product->addStock("current_stock");
        // $purchase->addStock($request->quantity);
        // $purchaseReal->addStock($request->quantity);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(PurchaseHistory $purchase)
    {
        // Remove stock from product
        $purchase->product->reduceStock($purchase->quantity);
        $purchaseReal = $this->getPurchase($purchase->created_at, $purchase->user_id);
        // return $purchaseReal->id;
        $purchaseAssignment = ProductAssignment::where("purchase_id", $purchaseReal->id)->count();
        if($purchaseAssignment > 0){
            return back()->withErrors("Sorry you can't delete this purchase becouse have a some product assignment!");
        }

        $purchase->delete();
        $purchaseReal->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
    }
}
