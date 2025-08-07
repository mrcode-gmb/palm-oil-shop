<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Expenses;
use App\Models\Purchase;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenses = Expenses::with(['product', 'user'])
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->supplier, fn($q) => $q->where('supplier_name', 'like', '%' . $request->supplier . '%'))
            ->latest()
            ->paginate(10);

        return view('expenses.index', compact('expenses'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $products = Purchase::with("product")->get();
        return view('expenses.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'Purchase_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $user_id = auth()->id();

        Expenses::create([
            'user_id' => $user_id,
            'product_id' => $request->product_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expenses $expenses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expenses $expenses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expenses $expenses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expenses $expenses)
    {
        //
    }
}
