<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Expenses;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $query = Sale::with(['product', 'user'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();
        
        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalProfit = $sales->sum('profit');
        $totalQuantity = $sales->sum('quantity');

        // Group by product
        $productSales = $sales->groupBy('product_id')->map(function ($group) {
            return [
                'product' => $group->first()->product,
                'quantity' => $group->sum('quantity'),
                'total_amount' => $group->sum('total_amount'),
                'profit' => $group->sum('profit'),
            ];
        });

        // Group by salesperson
        $salespersonSales = $sales->groupBy('user_id')->map(function ($group) {
            return [
                'user' => $group->first()->user,
                'quantity' => $group->sum('quantity'),
                'total_amount' => $group->sum('total_amount'),
                'profit' => $group->sum('profit'),
                'sales_count' => $group->count(),
            ];
        });

        $salespeople = User::where('role', 'salesperson')->get();

        return view('expenses.index', compact(
            'sales', 'startDate', 'endDate', 'totalSales', 'totalProfit', 
            'totalQuantity', 'productSales', 'salespersonSales', 'salespeople'
        ));
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
