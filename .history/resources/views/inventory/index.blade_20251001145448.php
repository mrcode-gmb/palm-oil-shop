<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ auth()->user()->isAdmin() ? 'Inventory Management' : 'My Assigned Products' }}
            </h2>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('inventory.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Add New Product
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="unit_type" class="block text-sm font-medium text-gray-700">Unit Type</label>
                            <select id="unit_type" 
                                    name="unit_type" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                <option value="Customize" {{ request('unit_type') == 'Customize' ? 'selected' : '' }}>Customize</option>
                                <option value="Uncustomize" {{ request('unit_type') == 'Uncustomize' ? 'selected' : '' }}>Uncustomize</option>
                            </select>
                        </div>

                        <div>
                            <label for="stock_filter" class="block text-sm font-medium text-gray-700">Stock Level</label>
                            <select id="stock_filter" 
                                    name="stock_filter" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Stock Levels</option>
                                <option value="available" {{ request('stock_filter') == 'available' ? 'selected' : '' }}>Available (> 0)</option>
                                <option value="low" {{ request('stock_filter') == 'low' ? 'selected' : '' }}>Low Stock (< 10)</option>
                                <option value="out" {{ request('stock_filter') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                    @else
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700">Assignment Status</label>
                            <select id="status_filter" 
                                    name="status_filter" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Active Assignments</option>
                                <option value="assigned" {{ request('status_filter') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in_progress" {{ request('status_filter') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    @endif

                    <div class="md:col-span-2 flex items-end space-x-2">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            Filter
                        </button>
                        <a href="{{ request()->url() }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
            <!-- Admin Inventory Summary -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Total Products</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $products->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Available Stock</p>
                            <p class="text-2xl font-bold text-green-600">{{ $products->where('quantity', '>', 0)->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Total Inventory Cost</p>
                            <p class="text-2xl font-bold text-green-600">₦{{ number_format($products->sum(fn($p) => $p->purchase_price * $p->quantity), 2) }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Low Stock</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $products->where('quantity', '<', $products->product->low_stock_threshold)->where('quantity', '>', 0)->count()  }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Staff Assignment Summary -->
            @if($assignments->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Total Assignments</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $assignments->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Assigned Quantity</p>
                            <p class="text-2xl font-bold text-green-600">{{ $assignments->sum('assigned_quantity') }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Sold Quantity</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $assignments->sum('sold_quantity') }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500">Remaining</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $assignments->sum('assigned_quantity') - $assignments->sum('sold_quantity') - $assignments->sum('returned_quantity') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if(auth()->user()->isAdmin())
            <!-- Admin Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg {{ $product->quantity == 0 ? 'border-l-4 border-red-500' : ($product->quantity < 10 ? 'border-l-4 border-yellow-500' : 'border-l-4 border-green-500') }}">
                    <div class="p-6">
                        <!-- Product Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $product->product->name }} <br> - ₦{{ $product->purchase_price }}</h3>
                                <p class="text-sm text-gray-500 capitalize">{{ $product->product->unit_type }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $product->quantity == 0 ? 'text-red-600' : ($product->quantity < 10 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ number_format($product->quantity, 1) }}
                                </p>
                                <p class="text-xs text-gray-500">units</p>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Purchase Price:</span>
                                <span class="text-sm font-medium">₦{{ number_format($product->purchase_price, 2) }}</span>
                            </div>
                            @if($product->notes)
                                <p class="text-sm text-gray-600">{{ $product->notes }}</p>
                            @endif
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-4">
                            @if($product->quantity == 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($product->quantity < 10)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Low Stock
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    In Stock
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('inventory.show', parameters: $product) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
                                View Details
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('inventory.edit', $product->product) }}" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
                                    Edit
                                </a>
                            @endif
                        </div>

                        <!-- Quick Actions for Admin -->
                        @if(auth()->user()->isAdmin())
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <div class="flex space-x-2 text-sm">
                                    <a href="{{ route('purchases.create') }}?product={{ $product->id }}" class="text-green-600 hover:text-green-500">
                                        Restock
                                    </a>
                                    @if($product->quantity > 0 && auth()->user()->isSalesperson())
                                        <a href="{{ route('sales.create') }}?product={{ $product->id }}" class="text-blue-600 hover:text-blue-500">
                                            Sell
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="md:col-span-3 bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first product.</p>
                        @if(auth()->user()->isAdmin())
                            <div class="mt-6">
                                <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Add Product
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforelse
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Staff Assignments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($assignments as $assignment)
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg {{ $assignment->isOverdue() ? 'border-l-4 border-red-500' : 'border-l-4 border-blue-500' }}">
                        <div class="p-6">
                            <!-- Assignment Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $assignment->purchase->product->name }}</h3>
                                    <p class="text-sm text-gray-500">Expected Price: ₦{{ number_format($assignment->expected_selling_price, 2) }}/unit</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @switch($assignment->status)
                                            @case('assigned') bg-yellow-100 text-yellow-800 @break
                                            @case('in_progress') bg-blue-100 text-blue-800 @break
                                            @case('completed') bg-green-100 text-green-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Assignment Stats -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Assigned:</span>
                                    <span class="font-medium">{{ $assignment->assigned_quantity }} units</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Sold:</span>
                                    <span class="font-medium text-green-600">{{ $assignment->sold_quantity }} units</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Remaining:</span>
                                    <span class="font-medium text-orange-600">{{ $assignment->remaining_quantity }} units</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Sales Revenue:</span>
                                    <span class="font-medium text-purple-600">₦{{ number_format($assignment->actual_total_sales, 2) }}</span>
                                </div>
                            </div>

                            <!-- Assignment Dates -->
                            <div class="text-xs text-gray-500 mb-4">
                                <div>Assigned: {{ $assignment->assigned_date->format('M d, Y') }}</div>
                                @if($assignment->due_date)
                                    <div>Due: {{ $assignment->due_date->format('M d, Y') }}
                                        @if($assignment->isOverdue())
                                            <span class="text-red-600 font-medium">(Overdue)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                @if(in_array($assignment->status, ['assigned', 'in_progress']) && $assignment->remaining_quantity > 0)
                                    <a href="{{ route('sales.create') }}?assignment_id={{ $assignment->id }}" 
                                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
                                        Sell Product
                                    </a>
                                @endif
                                <a href="{{ route('sales.assignments') }}" 
                                   class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
                                    View All
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-3 bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No assignments found</h3>
                            <p class="mt-1 text-sm text-gray-500">Contact your administrator for product assignments.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($assignments->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg">
                    {{ $assignments->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
</x-shop-layout>

