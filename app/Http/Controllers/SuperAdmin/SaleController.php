<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of all sales across all businesses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sales = Sale::with(['business', 'user', 'purchase.product'])
            ->latest()
            ->paginate(20);

        return view('super-admin.sales.index', compact('sales'));
    }

    /**
     * Display the specified sale.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        $sale->load(['business', 'user', 'purchase.product', 'assignment']);
        
        return view('sales.show', compact('sale'));
    }
}
