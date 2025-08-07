<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Expenses;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        

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
