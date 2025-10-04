<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Expenses;
use App\Models\Purchase;
use App\Exports\ExpensesPdfExport;
use Illuminate\Http\Request;
use PDF;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expenses::with(['user'])
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->supplier, fn($q) => $q->where('name', 'like', '%' . $request->supplier . '%'))
            ->latest();

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'supplier' => $request->supplier
            ];
            
            $expenses = $query->get();
            
            $pdf = PDF::loadView('exports.expenses-pdf', [
                'expenses' => $expenses,
                'filters' => $filters
            ])->setPaper('a4', 'portrait');
            
            return $pdf->download('expenses-report-' . now()->format('Y-m-d') . '.pdf');
        }

        $expenses = $query->paginate(10);
        
        // Get the current URL with all query parameters for export
        $exportUrl = url()->current() . '?' . http_build_query(array_merge(
            $request->query(),
            ['export' => 'pdf']
        ));

        return view('expenses.index', compact('expenses', 'exportUrl'));
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
        $today = Carbon::today();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $user_id = auth()->id();



        $todayProfit = Sale::whereDate('sale_date', $today)->sum('net_profit_per_unit');
        // return number_format($todayProfit,2);
        Expenses::create([
            'user_id' => $user_id,
            'name' => $request->name,
            'today_profit' => $todayProfit,
            'amount' => $request->amount,
            'today_net_profit' => $todayProfit - $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expenses $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expenses $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expenses $expense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Only update the fields that are in the form
        $expense->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses.index')
                        ->with('success', 'Expense updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expenses $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
                        ->with('success', 'Expense deleted successfully');
    }
}
