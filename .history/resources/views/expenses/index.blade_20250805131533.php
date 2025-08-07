<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Expenses
            </h2>
            <a href="{{ route('expenses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Record New Expense
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <input type="text" name="supplier" id="supplier" value="{{ request('supplier') }}" placeholder="Supplier name"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-blue-500">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium mr-2">
                            Filter
                        </button>
                        <a href="{{ route('expenses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        @if($expenses->count())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500">Total Expenses</p>
                    <p class="text-2xl font-bold text-blue-600">₦{{ number_format($expenses->sum('total_cost'), 2) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500">Total Quantity</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($expenses->sum('quantity'), 1) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $expenses->count() }}</p>
                </div>
            </div>
        @endif

        <!-- Expenses Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>                    
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $expense->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $expense->name}}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $expense->supplier_name ?? '-' }}<br>
                                <span class="text-sm text-gray-500">{{ $expense->supplier_phone ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ number_format($expense->quantity, 1) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                ₦{{ number_format($expense->cost_price_per_unit, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-blue-600 font-medium whitespace-nowrap">
                                ₦{{ number_format($expense->total_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ optional($expense->user)->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{-- Optional action buttons --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No expenses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($expenses->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $expenses->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-shop-layout>
