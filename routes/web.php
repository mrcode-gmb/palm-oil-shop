<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductAssignmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
    // return view("welcome");
});

// Redirect authenticated users based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect('/admin/dashboard');
    } elseif ($user->role === 'salesperson') {
        return redirect('/sales/dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    
    Route::get('/my-staffs', [DashboardController::class, 'myStaff'])->name('admin.myStaff');
    Route::get('/create/staff-account', [RegisteredUserController::class, 'createUser'])->name('admin.createUser');
    Route::post('/store/staff-account', [RegisteredUserController::class, 'storeUser'])->name('admin.storeUser');
    Route::get('/staff/{user}', [RegisteredUserController::class, 'showUser'])->name('admin.showUser');
    Route::get('/staff/{user}/edit', [RegisteredUserController::class, 'editUser'])->name('admin.editUser');
    Route::patch('/staff/{user}', [RegisteredUserController::class, 'updateUser'])->name('admin.updateUser');
    Route::patch('/staff/{user}/toggle-status', [RegisteredUserController::class, 'toggleStatus'])->name('admin.toggleUserStatus');
    
    // Product Assignment Routes (Admin only)
    Route::get('/assignments', [ProductAssignmentController::class, 'index'])->name('admin.assignments.index');
    Route::get('/assignments/create', [ProductAssignmentController::class, 'create'])->name('admin.assignments.create');
    Route::post('/assignments', [ProductAssignmentController::class, 'store'])->name('admin.assignments.store');
    Route::get('/assignments/{assignment}', [ProductAssignmentController::class, 'show'])->name('admin.assignments.show');
    Route::patch('/assignments/{assignment}/collect-return', [ProductAssignmentController::class, 'collectReturn'])->name('admin.assignments.collectReturn');
    // Inventory Management (Admin only)
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');
    Route::get('/inventory/{adjustment}/adjust-stock/delete', [InventoryController::class, 'adjustStockDelete'])->name('inventory.adjustStockDelete');
    
    // Purchase Management (Admin only)
    Route::resource('purchases', PurchaseController::class);
    
    // Sales Management (Admin can view/edit all)
    // Route::resource('sales', SalesController::class);
    
    // Reports (Admin only)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/profit', [ReportController::class, 'profitReport'])->name('reports.profit');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPDF'])->name('reports.sales.pdf');
    
    
    // Expensive (Admin only)
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
    Route::post('/expenses/store', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/inventory', [ExpensesController::class, 'inventoryReport'])->name('expenses.inventory');
    Route::get('/reports/expensive/pdf', [ReportController::class, 'exportExpensivePDF'])->name('reports.expensive.pdf');

    // app settings (Admin only)

    Route::get('/gallery/app-settings', [AppSettingController::class, 'index'])->name('appSetting.index');
    Route::get('/gallery/app-settings/news', [AppSettingController::class, 'indexNews'])->name('appSetting.indexNews');
    Route::get('/app-setting/create', [AppSettingController::class, 'create'])->name('appSetting.create');
    Route::get('/app-setting/create/new', [AppSettingController::class, 'createNew'])->name('appSetting.createNew');
    Route::get('/app-setting/delete/{new}', [AppSettingController::class, 'newDelete'])->name('appSetting.newDelete');
    Route::get('/app-setting/gallery-delete/{gallery}', [AppSettingController::class, 'galleryDelete'])->name('appSetting.galleryDelete');
    Route::post('/app-setting/store', [AppSettingController::class, 'store'])->name('appSetting.store');
    Route::post('/app-setting/store/news', [AppSettingController::class, 'storeNews'])->name('appSetting.storeNews');
    // Route::get('/reports/expensive/pdf', [ReportController::class, 'exportExpensivePDF'])->name('reports.expensive.pdf');

});

Route::middleware(['auth', 'role:admin,salesperson'])->prefix('admin')->group(function () {
    Route::resource('sales', SalesController::class);
});

// Salesperson Routes
Route::middleware(['auth', 'role:salesperson'])->prefix('sales')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesDashboard'])->name('sales.dashboard');
    
    // Sales (Salesperson can only create and view their own)
    Route::get('/my-sales', [SalesController::class, 'index'])->name('sales.my-sales');
    Route::get('/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/store', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.show');
    
    // View available inventory (read-only)
    Route::get('/inventory/index', [InventoryController::class, 'index'])->name('sales.inventory');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('sales.inventory.show');
    
    // Product Assignments for Staff
    Route::get('/my-assignments', [ProductAssignmentController::class, 'myAssignments'])->name('sales.assignments');
});

// Shared Routes (Both Admin and Salesperson)
Route::middleware(['auth', 'role:admin,salesperson'])->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});





Route::get('/app-setting/fetch/api', [AppSettingController::class, 'fetchApi'])->name('appSetting.fetchApi');
Route::get('/app-setting/fetch/api/news', [AppSettingController::class, 'fetchNewApi'])->name('appSetting.fetchNewApi');
require __DIR__.'/auth.php';
