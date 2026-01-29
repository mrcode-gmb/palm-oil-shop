<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Expenses;
use App\Models\Purchase;
use App\Exports\ExpensesPdfExport;
use App\Traits\BusinessScoped;
use Illuminate\Http\Request;
use PDF;

class ExpensesController extends Controller
{
    use BusinessScoped;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->scopeToCurrentBusiness(Expenses::class)->with(['user'])
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->supplier, fn($q) => $q->where('name', 'like', '%' . $request->supplier . '%'))
            ->latest();
            $query2 = $this->scopeToCurrentBusiness(ProductAssignment::class)->with(['user', 'purchase.product', 'collectionHistories', 'salePrices']);
            $quantities = $query->where("status", "!=", "completed")->get()->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'user_name' => $assignment->user->name,
                    'product_id' => $assignment->product_id,
                    'product_name' => $assignment->purchase->product->name,
                    'assigned_quantity' => $assignment->assigned_quantity,
                    'sold_quantity' => $assignment->sold_quantity,
                    'returned_quantity' => $assignment->returned_quantity,
                    'quantity' => $assignment->assigned_quantity - $assignment->returned_quantity - $assignment->sold_quantity,
                    'expected_selling_price' => $assignment->expected_selling_price,
                    'commission_rate' => $assignment->commission_rate,
                    'due_date' => $assignment->due_date,
                    'status' => $assignment->status
    
                ];
            });
            foreach ($quantities as $quantity) {
                if($quantity['status'] != 'completed' && $quantity['quantity'] == 0){
                    // $quantity['status'] = 'completed';
                    ProductAssignment::where("id", $quantity['id'])->update([
                        'status' => 'completed'
                    ]);
                }
            }
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



        $todayProfit = $this->scopeToCurrentBusiness(Sale::class)
            ->whereDate('sale_date', $today)
            ->sum('net_profit_per_unit');
        
        // Create expense with business_id
        $data = $this->addBusinessId([
            'user_id' => $user_id,
            'name' => $request->name,
            'today_profit' => $todayProfit,
            'amount' => $request->amount,
            'today_net_profit' => $todayProfit - $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);
        
        Expenses::create($data);

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
