<x-shop-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Creditors
        </h2>
    </x-slot>
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.creditors.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md">Add Creditor</a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($creditors as $creditor)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $creditor->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $creditor->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $creditor->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">₦{{ number_format($creditor->balance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.creditors.show', $creditor) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No creditors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $creditors->links() }}
    </div>
</x-shop-layout>

