<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory Management
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
                    <div>
                        <label for="unit_type" class="block text-sm font-medium text-gray-700">Unit Type</label>
                        <select id="unit_type" 
                                name="unit_type" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            <option value="litre" {{ request('unit_type') == 'litre' ? 'selected' : '' }}>Litre</option>
                            <option value="jerrycan" {{ request('unit_type') == 'jerrycan' ? 'selected' : '' }}>Jerrycan</option>
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

        <!-- Inventory Summary -->
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
                        <p class="text-2xl font-bold text-green-600">{{ $products->where('current_stock', '>', 0)->count() }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">Low Stock</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $products->where('current_stock', '<', 10)->where('current_stock', '>', 0)->count() }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">Out of Stock</p>
                        <p class="text-2xl font-bold text-red-600">{{ $products->where('current_stock', '=', 0)->count() }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($products as $product)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg {{ $product->current_stock == 0 ? 'border-l-4 border-red-500' : ($product->current_stock < 10 ? 'border-l-4 border-yellow-500' : 'border-l-4 border-green-500') }}">
                    <div class="p-6">
                        <!-- Product Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500 capitalize">{{ $product->unit_type }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $product->current_stock == 0 ? 'text-red-600' : ($product->current_stock < 10 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ number_format($product->current_stock, 1) }}
                                </p>
                                <p class="text-xs text-gray-500">units</p>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Selling Price:</span>
                                <span class="text-sm font-medium">₦{{ number_format($product->selling_price, 2) }}</span>
                            </div>
                            @if($product->description)
                                <p class="text-sm text-gray-600">{{ $product->description }}</p>
                            @endif
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-4">
                            @if($product->current_stock == 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($product->current_stock < 10)
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
                            <a href="{{ route('inventory.show', ) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
                                View Details
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('inventory.edit', $product) }}" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200">
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
                                    @if($product->current_stock > 0 && auth()->user()->isSalesperson())
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
    </div>
</x-shop-layout>

