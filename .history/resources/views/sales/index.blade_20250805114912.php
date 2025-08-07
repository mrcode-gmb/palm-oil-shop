<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ auth()->user()->isAdmin() ? 'All Sales' : 'My Sales' }}
            </h2>
            @if(auth()->user()->isSalesperson())
                <a href="{{ route('sales.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Record New Sale
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
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date') }}" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date') }}" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Salesperson</label>
                            <select id="user_id" 
                                    name="user_id" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Salespeople</option>
                                @foreach($salespeople as $person)
                                    <option value="{{ $person->id }}" {{ request('user_id') == $person->id ? 'selected' : '' }}>
                                        {{ $person->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 mr-2">
                            Filter
                        </button>
                        <a href="{{ request()->url() }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales Summary -->
        @if($sales->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">Total Sales</p>
                        <p class="text-2xl font-bold text-green-600">₦{{ number_format($sales->sum('total_amount'), 2) }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">Total Profit</p>
                        <p class="text-2xl font-bold text-blue-600">₦{{ number_format($sales->sum('profit'), 2) }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $sales->count() }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Sales Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">seller_profit_per_unit</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salesperson</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sale->sale_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $sale->product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($sale->product->unit_type) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($sale->quantity, 1) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦{{ number_format($sale->selling_price_per_unit, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ₦{{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    ₦{{ number_format($sale->profit, 2) }}
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sale->user->name }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sale->customer_name ?: 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('sales.edit', $sale) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this sale?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? '9' : '8' }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No sales found.
                                    @if(auth()->user()->isSalesperson())
                                        <a href="{{ route('sales.create') }}" class="text-blue-600 hover:text-blue-500 ml-1">Record your first sale</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($sales->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-shop-layout>

