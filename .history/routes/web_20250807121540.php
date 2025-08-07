<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect authenticated users based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'salesperson') {
        return redirect()->route('sales.dashboard');
    }
    abort(403, 'Unauthorized');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/my-staffs', [DashboardController::class, 'myStaff'])->name('admin.myStaff');

    // Inventory
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');

    // Purchases
    Route::resource('purchases', PurchaseController::class);

    // Sales (Admin can view/edit all sales)
    Route::get('/sales', [SalesController::class, 'index'])->name('admin.sales.index');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('admin.sales.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/profit', [ReportController::class, 'profitReport'])->name('reports.profit');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPDF'])->name('reports.sales.pdf');

    // Expenses
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
    Route::post('/expenses/store', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/inventory', [ExpensesController::class, 'inventoryReport'])->name('expenses.inventory');
    Route::get('/expenses/sales/pdf', [ExpensesController::class, 'exportSalesPDF'])->name('expenses.sales.pdf');
});

// Salesperson Routes
Route::middleware(['auth', 'role:salesperson'])->prefix('sales')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesDashboard'])->name('sales.dashboard');

    // Sales (create/view own)
    Route::get('/my-sales', [SalesController::class, 'index'])->name('sales.my-sales');
    Route::get('/create', [SalesController::class, 'create'])->name('salesperson.sales.create'); // renamed to avoid conflict
    Route::post('/store', [SalesController::class, 'store'])->name('salesperson.sales.store');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('salesperson.sales.show');

    // Inventory (read-only)
    Route::get('/inventory/index', [InventoryController::class, 'index'])->name('sales.inventory');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('sales.inventory.show');
});

// Shared Routes (Admin and Salesperson)
Route::middleware(['auth', 'role:admin,salesperson'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
