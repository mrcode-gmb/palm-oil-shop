<x-shop-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add New Product
        </h2>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('inventory.store') }}" class="space-y-6">
                    @csrf

                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Staff Name</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                 
                    <!-- Supplier Name -->
                    <div>
                        <label for="supplier_name" class="block text-sm font-medium text-gray-700">Staff Email</label>
                        <input type="text" name="supplier_name" id="supplier_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        @error('supplier_name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Form Actions -->
                    <div class="flex justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md">Cancel</a>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-md">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-shop-layout>
