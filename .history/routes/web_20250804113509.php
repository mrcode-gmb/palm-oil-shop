<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect('/admin/dashboard');
    } elseif ($user->role === 'salesperson') {
        return redirect('/sales/dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ------------------ ADMIN ROUTES ------------------ //
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

    // Inventory & Purchases
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');
    Route::resource('purchases', PurchaseController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/profit', [ReportController::class, 'profitReport'])->name('reports.profit');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPDF'])->name('reports.sales.pdf');

    // View sales (read-only for admin)
    Route::get('/sales', [SalesController::class, 'adminIndex'])->name('admin.sales.index');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('admin.sales.show');
});

// ------------------ SALESPERSON ROUTES ------------------ //
Route::middleware(['auth', 'role:salesperson'])->prefix('sales')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesDashboard'])->name('sales.dashboard');

    // Sales
    Route::get('/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/store', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/my-sales', [SalesController::class, 'index'])->name('sales.my-sales');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.show');

    // View inventory (read-only)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('sales.inventory');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('sales.inventory.show');
});

// ------------------ SHARED ROUTES ------------------ //
Route::middleware(['auth', 'role:admin,salesperson'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
