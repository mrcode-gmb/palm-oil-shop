<x-shop-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reports & Analytics
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Sales Report -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Sales Report</h3>
                            <p class="text-sm text-gray-500">Detailed sales analysis and performance</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 mb-4">View comprehensive sales data, filter by date range, salesperson, and product. Export to PDF for record keeping.</p>
                        <a href="{{ route('reports.sales') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition-colors duration-200">
                            View Sales Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profit Report -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Profit Analysis</h3>
                            <p class="text-sm text-gray-500">Monthly and yearly profit tracking</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 mb-4">Analyze profit margins, track monthly performance, and identify trends in your business profitability.</p>
                        <a href="{{ route('reports.profit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-200">
                            View Profit Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Inventory Report -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Inventory Report</h3>
                            <p class="text-sm text-gray-500">Stock levels and inventory valuation</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 mb-4">Monitor stock levels, inventory value, and identify products that need restocking.</p>
                        <a href="{{ route('reports.inventory') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition-colors duration-200">
                            View Inventory Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">₦{{ number_format(\App\Models\Sale::whereDate('created_at', today())->sum('total_amount'), 2) }}</p>
                        <p class="text-sm text-gray-500">Today's Sales</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">₦{{ number_format(\App\Models\Sale::whereDate('created_at', today())->sum('profit'), 2) }}</p>
                        <p class="text-sm text-gray-500">Today's Profit</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">₦{{ number_format(\App\Models\Sale::whereMonth('created_at', now()->month)->sum('total_amount'), 2) }}</p>
                        <p class="text-sm text-gray-500">Monthly Sales</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ \App\Models\Product::where('current_stock', '<', 10)->count() }}</p>
                        <p class="text-sm text-gray-500">Low Stock Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Selling Products -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Selling Products (This Month)</h3>
                    <div class="space-y-3">
                        @php
                            $topProducts = \App\Models\Sale::with('purchase.')
                                ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total_amount) as total_revenue')
                                ->whereMonth('created_at', now()->month)
                                ->groupBy('product_id')
                                ->orderBy('total_sold', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($topProducts as $item)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($item->total_sold, 1) }} units sold</p>
                                </div>
                                <p class="text-sm font-medium text-green-600">₦{{ number_format($item->total_revenue, 0) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No sales data available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top Performing Salespeople -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performers (This Month)</h3>
                    <div class="space-y-3">
                        @php
                            $topSalespeople = \App\Models\Sale::with('user')
                                ->selectRaw('user_id, SUM(total_amount) as total_sales, SUM(profit) as total_profit, COUNT(*) as sales_count')
                                ->whereMonth('created_at', now()->month)
                                ->groupBy('user_id')
                                ->orderBy('total_sales', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($topSalespeople as $performer)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $performer->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $performer->sales_count }} transactions</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-green-600">₦{{ number_format($performer->total_sales, 0) }}</p>
                                    <p class="text-xs text-blue-600">₦{{ number_format($performer->total_profit, 0) }} profit</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No sales data available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Export Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('reports.sales.pdf') }}" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Sales PDF
                    </a>
                    <button class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition-colors duration-200" onclick="alert('Excel export feature coming soon!')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export to Excel
                    </button>
                    <button class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-200" onclick="window.print()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>

