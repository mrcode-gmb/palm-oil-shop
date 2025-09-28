<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Staff Details - {{ $user->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.editUser', $user) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Edit Staff
                </a>
                <a href="{{ route('admin.myStaff') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Back to Staff List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Staff Information Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Staff Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $user->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($user->status ?? 'active') }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Performance Card -->
        @if($user->isSalesperson())
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Summary</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $user->sales()->count() }}</div>
                        <div class="text-sm text-blue-600">Total Sales</div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">₦{{ number_format($user->sales()->sum('total_amount'), 2) }}</div>
                        <div class="text-sm text-green-600">Total Revenue</div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">₦{{ number_format($user->sales()->sum('profit'), 2) }}</div>
                        <div class="text-sm text-purple-600">Total Profit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Sales</h3>
                
                @if($user->sales()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->sales()->with('purchase.product')->latest()->limit(10)->get() as $sale)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sale->sale_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sale->purchase->product->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sale->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦{{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦{{ number_format($sale->profit, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No sales recorded yet.</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                
                <div class="flex space-x-4">
                    <form method="POST" action="{{ route('admin.toggleUserStatus', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200
                                {{ $user->isActive() ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white' }}"
                                onclick="return confirm('Are you sure you want to {{ $user->isActive() ? 'deactivate' : 'activate' }} this staff member?')">
                            {{ $user->isActive() ? 'Deactivate' : 'Activate' }} Staff
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>
