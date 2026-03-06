<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\BusinessScoped;
use App\Models\PurchaseHistory;
use App\Models\ProductAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    use BusinessScoped;

    /**
     * Display a listing of purchases
     */

    private function getPurchase(PurchaseHistory $history)
    {
        return $this->scopeToCurrentBusiness(Purchase::class)
            ->with(['product', 'user'])
            ->where('created_at', $history->created_at)
            ->where('user_id', $history->user_id)
            ->where('product_id', $history->product_id)
            ->first();
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

    public function restock($purchase)
    {

        $purchase = $this->scopeToCurrentBusiness(Purchase::class)->with('product')->where("quantity", ">", 0)->where("id", $purchase)->first();


        return view('purchases.restock', compact('purchase'));
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
            'reference' => 'PURCHASE-' . strtoupper(Str::random(10)),
            'description' => "Purchase product history",
            'status' => 'completed',
            'metadata' => $data
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully!');
    }
    public function storeRestock(Request $request)
    {

        // return $request;
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0.01',
            'buying_price_per_unit' => 'required|numeric|min:0',
            'selling_profit_per_unit' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $totalCost = $request->quantity * $request->buying_price_per_unit;


        $purchase = Purchase::where("id", $request->purchase_id)->first();

        // Create the purchase with business_id
        $data = $this->addBusinessId([
            'product_id' => $purchase->product_id,
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
        $purchase->quantity += $request->quantity;
        $purchase->total_cost += $totalCost;
        $purchase->save();
        $purchaseHistories = PurchaseHistory::create($data);

        // Update product stock
        $product = Product::findOrFail($purchase->product_id);
        $product->addStock($request->quantity);

        $businessWallet = auth()->user()->business->wallet;
        $businessWallet->balance -= $totalCost;
        $businessWallet->save();

        $transaction = $businessWallet->transactions()->create([
            'wallet_id' => $businessWallet->id,
            'business_id'=> auth()->user()->business->id,
            'amount' => $totalCost,
            'type' => "debit",
            'reference' => 'PURCHASE-' . strtoupper(Str::random(10)),
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
        $purchaseReal = $this->getPurchase($purchase);
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
        $purchaseReal = $this->getPurchase($purchase);
        return view('purchases.edit', compact('purchase', 'products'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, PurchaseHistory $purchase)
    {
        // return $purchase;
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0.01',
            'cost_price_per_unit' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $lockedHistory = $this->scopeToCurrentBusiness(PurchaseHistory::class)
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchaseReal = $this->getPurchase($lockedHistory);
            if (!$purchaseReal) {
                throw ValidationException::withMessages([
                    'quantity' => 'Unable to find matching inventory record for this purchase history row.',
                ]);
            }

            $lockedPurchase = $this->scopeToCurrentBusiness(Purchase::class)
                ->whereKey($purchaseReal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $product = $this->scopeToCurrentBusiness(Product::class)
                ->whereKey($lockedHistory->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $newHistoryQuantity = (float) $request->quantity;
            $oldHistoryQuantity = (float) $lockedHistory->quantity;
            $quantityDelta = $newHistoryQuantity - $oldHistoryQuantity;
            $newPurchasePrice = (float) $request->cost_price_per_unit;
            $newHistoryTotalCost = $newHistoryQuantity * $newPurchasePrice;
            $costDelta = $newHistoryTotalCost - (float) $lockedHistory->total_cost;
            $newPurchaseQuantity = (float) $lockedPurchase->quantity + $quantityDelta;
            $newPurchaseTotalCost = (float) $lockedPurchase->total_cost + $costDelta;
            $newStock = (float) $product->current_stock + $quantityDelta;

            if ($newStock < 0 || $newPurchaseQuantity < 0 || $newPurchaseTotalCost < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock update would result in negative product stock.',
                ]);
            }

            // Keep historical quantity/cost immutable; only metadata remains editable.
            $lockedHistory->update([
                'supplier_name' => $request->supplier_name,
                'supplier_phone' => $request->supplier_phone,
                'purchase_date' => $request->purchase_date,
                'notes' => $request->notes,
            ]);

            $lockedPurchase->update([
                'supplier_name' => $request->supplier_name,
                'supplier_phone' => $request->supplier_phone,
                'quantity' => $newPurchaseQuantity,
                'purchase_price' => $newPurchaseQuantity > 0 ? $newPurchaseTotalCost / $newPurchaseQuantity : $newPurchasePrice,
                'total_cost' => $newPurchaseTotalCost,
                'purchase_date' => $request->purchase_date,
                'notes' => $request->notes,
            ]);

            $product->update([
                'current_stock' => $newStock,
            ]);

            if ($quantityDelta != 0.0 || $costDelta != 0.0) {
                PurchaseHistory::create($this->addBusinessId([
                    'product_id' => $lockedHistory->product_id,
                    'user_id' => auth()->id(),
                    'supplier_name' => $request->supplier_name,
                    'supplier_phone' => $request->supplier_phone,
                    'quantity' => $quantityDelta,
                    'purchase_price' => $newPurchasePrice,
                    'total_cost' => $costDelta,
                    'selling_price' => $lockedHistory->selling_price ?? 0,
                    'seller_profit' => $lockedHistory->seller_profit ?? 0,
                    'purchase_date' => now()->toDateString(),
                    'notes' => trim('ADJUSTMENT_FROM_EDIT:' . $lockedHistory->id . ' ' . ($request->notes ?? '')),
                ]));
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(PurchaseHistory $purchase)
    {
        // Remove stock from product
        $purchase->product->reduceStock($purchase->quantity);
        $purchaseReal = $this->getPurchase($purchase);
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
