<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Product Assignments
            </h2>
            <a href="{{ route('admin.assignments.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Assign Product to Staff
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Assignments Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignments as $assignment)
                            <tr class="hover:bg-gray-50 {{ $assignment->isOverdue() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $assignment->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $assignment->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $assignment->purchase->product->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">₦{{ number_format($assignment->expected_selling_price, 2) }}/unit</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->assigned_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->sold_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->remaining_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @switch($assignment->status)
                                            @case('assigned') bg-yellow-100 text-yellow-800 @break
                                            @case('in_progress') bg-blue-100 text-blue-800 @break
                                            @case('completed') bg-green-100 text-green-800 @break
                                            @case('returned') bg-gray-100 text-gray-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                    </span>
                                    @if($assignment->isOverdue())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 ml-1">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'No due date' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        @if($assignment->status !== 'completed')
                                            <button onclick="showCollectModal({{ $assignment->id }}, '{{ $assignment->user->name }}', '{{ $assignment->purchase->product->name ?? 'N/A' }}')"
                                                    class="text-green-600 hover:text-green-900">Collect Return</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No product assignments found.
                                    <a href="{{ route('admin.assignments.create') }}"
                                        class="text-blue-600 hover:text-blue-500 ml-1">Create your first assignment</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($assignments->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $assignments->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Collect Return Modal -->
    <div id="collectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Collect Return</h3>
                <form id="collectForm" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Staff & Product</label>
                        <p id="staffProduct" class="text-sm text-gray-900 mt-1"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="returned_quantity" class="block text-sm font-medium text-gray-700">Returned Quantity</label>
                        <input type="number" step="0.01" name="returned_quantity" id="returned_quantity" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCollectModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md">Cancel</button>
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded-md">Collect Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showCollectModal(assignmentId, staffName, productName) {
            document.getElementById('staffProduct').textContent = staffName + ' - ' + productName;
            document.getElementById('collectForm').action = `/admin/assignments/${assignmentId}/collect-return`;
            document.getElementById('collectModal').classList.remove('hidden');
        }

        function hideCollectModal() {
            document.getElementById('collectModal').classList.add('hidden');
            document.getElementById('collectForm').reset();
        }
    </script>
</x-shop-layout>
