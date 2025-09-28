<?php

namespace App\Http\Controllers;

use App\Models\ProductAssignment;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductAssignmentController extends Controller
{
    /**
     * Display a listing of product assignments
     */
    public function index()
    {
        $assignments = ProductAssignment::with(['user', 'purchase.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new assignment
     */
    public function create()
    {
        $staff = User::where('role', 'salesperson')->where('status', 'active')->get();
        $products = Purchase::with('product')->where('quantity', '>', 0)->get();

        return view('assignments.create', compact('staff', 'products'));
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'purchase_id' => 'required|exists:purchases,id',
            'assigned_quantity' => 'required|numeric|min:0.01',
            'expected_selling_price' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if enough quantity is available
        $purchase = Purchase::findOrFail($request->purchase_id);
        $assignedQuantity = ProductAssignment::where('purchase_id', $request->purchase_id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->sum('assigned_quantity');
        
        $availableQuantity = $purchase->quantity - $assignedQuantity;
        
        if ($request->assigned_quantity > $availableQuantity) {
            return back()->withErrors(['assigned_quantity' => 'Not enough quantity available. Available: ' . $availableQuantity]);
        }

        ProductAssignment::create([
            'user_id' => $request->user_id,
            'purchase_id' => $request->purchase_id,
            'assigned_quantity' => $request->assigned_quantity,
            'expected_selling_price' => $request->expected_selling_price,
            'assigned_date' => Carbon::today(),
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'status' => 'assigned',
        ]);

        return redirect()->route('admin.assignments.index')->with('success', 'Product assigned successfully!');
    }

    /**
     * Display the specified assignment
     */
    public function show(ProductAssignment $assignment)
    {
        $assignment->load(['user', 'purchase.product', 'sales']);
        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show assignments for the authenticated staff member
     */
    public function myAssignments()
    {
        $user = auth()->user();
        $assignments = $user->assignments()->with(['purchase.product', 'sales'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('assignments.my-assignments', compact('assignments'));
    }

    /**
     * Mark assignment as returned and collect remaining products
     */
    public function collectReturn(Request $request, ProductAssignment $assignment)
    {
        $request->validate([
            'returned_quantity' => 'required|numeric|min:0',
            'profit_collected' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment->update([
            'returned_quantity' => $request->returned_quantity,
            'profit_collected' => $request->profit_collected,
            'returned_date' => Carbon::today(),
            'status' => 'completed',
            'notes' => $assignment->notes . "\n\nReturn Notes: " . $request->notes,
        ]);

        // Update inventory - add returned quantity back to purchase
        $assignment->purchase->increment('quantity', $request->returned_quantity);

        return redirect()->route('admin.assignments.show', $assignment)
            ->with('success', 'Return processed successfully! Inventory updated.');
    }
}
