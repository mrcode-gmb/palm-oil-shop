<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\BusinessScoped;

class CreditorController extends Controller
{
    use BusinessScoped;

    public function index()
    {
        $businessId = $this->getBusinessId();
        $business = \App\Models\Business::findOrFail($businessId);
        $creditors = $business->creditors()->paginate(10);
        // return $creditors;
        return view('admin.creditors.index', compact('creditors'));
    }

    public function create()
    {
        return view('admin.creditors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $businessId = $this->getBusinessId();
        $business = \App\Models\Business::findOrFail($businessId);
        $business->creditors()->create($request->all());

        return redirect()->route('admin.creditors.index')->with('success', 'Creditor created successfully.');
    }

    public function show(\App\Models\Creditor $creditor)
    {
        $this->authorize('view', $creditor);
        $transactions = $creditor->transactions()->latest()->paginate(10);
        $sales = $creditor->sales()->with('purchase.product', 'user')->latest()->paginate(10, ['*'], 'sales');

        $total_credit = $creditor->transactions()->where('type', 'debit')->sum('amount');
        $total_paid = $creditor->transactions()->where('type', 'credit')->sum('amount');

        return view('admin.creditors.show', compact('creditor', 'transactions', 'sales', 'total_credit', 'total_paid'));
    }

    public function recordPayment(Request $request, \App\Models\Creditor $creditor)
    {
        $this->authorize('update', $creditor);
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);
        if($creditor->balance < $request->amount){
            return back()->withErrors('The amount is not grater than the current balance.');
        }
        $creditor->balance -= $request->amount;
        $creditor->save();
        

        $creditor->transactions()->create([
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Payment received',
            'running_balance' => $creditor->balance,
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function getBusinessId()
    {
        return auth()->user()->business_id;
    }
}
