<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductAssignment;
use App\Models\User;
use App\Models\Purchase;
use App\Traits\BusinessScoped;
use Carbon\Carbon;

class ProductAssignmentController extends Controller
{
    use BusinessScoped;

    /**
     * Display a listing of product assignments
     */
    public function index(Request $request)
    {
        $query = $this->scopeToCurrentBusiness(ProductAssignment::class)->with(['user', 'purchase.product']);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active');
            } elseif ($request->status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($request->status === 'overdue') {
                $query->where('due_date', '<', now())->where('status', 'active');
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            $filters = [
                'user_id' => $request->user_id,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ];
            
            $assignments = $query->get();
            
            $pdf = \PDF::loadView('exports.assignments-pdf', [
                'assignments' => $assignments,
                'filters' => $filters
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('assignments-report-' . now()->format('Y-m-d') . '.pdf');
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get users for filter dropdown - only from current business
        $users = $this->scopeToCurrentBusiness(User::class)
            ->where('role', 'salesperson')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get the current URL with all query parameters for export
        $exportUrl = url()->current() . '?' . http_build_query(array_merge(
            $request->query(),
            ['export' => 'pdf']
        ));

        return view('assignments.index', compact('assignments', 'users', 'exportUrl'));
    }

    /**
     * Show the form for creating a new assignment
     */
    public function create()
    {
        $staff = $this->scopeToCurrentBusiness(User::class)
            ->where('role', 'salesperson')
            ->where('status', 'active')
            ->get();
        $products = $this->scopeToCurrentBusiness(Purchase::class)
            ->with('product')
            ->where('quantity', '>', 0)
            ->get();

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
            'assigned_quantity' => 'required|numeric|min:1',
            'expected_selling_price' => 'required|numeric|min:10',
            'commission_rate' => 'required|numeric|min:0',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if enough quantity is available in the purchase
        $purchase = $this->scopeToCurrentBusiness(Purchase::class)->findOrFail($request->purchase_id);
        
        // Calculate already assigned quantity
        $assignedQuantity = $this->scopeToCurrentBusiness(ProductAssignment::class)
            ->where('purchase_id', $request->purchase_id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->sum('assigned_quantity');
            
        $availableQuantity = $purchase->quantity - $assignedQuantity;
        
        if ($request->assigned_quantity > $availableQuantity) {
            return back()->withErrors(['assigned_quantity' => 'Not enough quantity available. Available: ' . $availableQuantity]);
        }

        // Calculate commission amount
        $totalExpectedSales = $request->assigned_quantity * $request->expected_selling_price;
        

        // Create the assignment with business_id
        $data = $this->addBusinessId([
            'user_id' => $request->user_id,
            'purchase_id' => $request->purchase_id,
            'assigned_quantity' => $request->assigned_quantity,
            'expected_selling_price' => $request->expected_selling_price,
            'commission_rate' => $request->commission_rate,
            'commission_amount' => $request->commission_rate,
            'assigned_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'assigned',
            'notes' => $request->notes,
        ]);
        
        $assignment = ProductAssignment::create($data);

        // Update inventory
        $purchase->decrement('quantity', $request->assigned_quantity);
        $purchase->product->decrement('current_stock', $request->assigned_quantity);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Product assigned successfully!');
    }
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
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment->update([
            'returned_quantity' => $request->returned_quantity,
            'profit_collected' => 0.00,
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
