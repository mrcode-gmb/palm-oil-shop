<x-shop-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Product: {{ $product->name }}
            </h2>
            <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Inventory
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('inventory.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Product Name -->
                    <div>
                        <x-input-label for="name" :value="__('Inventory Name')" />
                        <x-text-input id="name" 
                                      name="name" 
                                      type="text" 
                                      class="mt-1 block w-full" 
                                      :value="old('name', $product->name)" 
                                      required 
                                      autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <!-- Unit Type -->
                    <div>
                        <x-input-label for="unit_type" :value="__('Unit Type')" />
                        <select id="unit_type" 
                                name="unit_type" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                required>
                            <option value="">Select Unit Type</option>
                            <option value="litre" {{ old('unit_type', $product->unit_type) == 'litre' ? 'selected' : '' }}>Litre</option>
                            <option value="jerrycan" {{ old('unit_type', $product->unit_type) == 'jerrycan' ? 'selected' : '' }}>Jerrycan</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('unit_type')" />
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <x-input-label for="selling_price" :value="__('Selling Price (₦)')" />
                        <x-text-input id="selling_price" 
                                      name="selling_price" 
                                      type="number" 
                                      step="0.01" 
                                      min="0" 
                                      class="mt-1 block w-full" 
                                      :value="old('selling_price', $product->selling_price)" 
                                      required />
                        <x-input-error class="mt-2" :messages="$errors->get('selling_price')" />
                    </div>

                    <!-- Description -->
                    <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                        <textarea id="description" 
                                  name="description" 
                                  rows="3" 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Optional product description">{{ old('description', $product->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <!-- Current Stock Info (Read-only) -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Current Stock Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Current Stock:</p>
                                <p class="text-lg font-semibold {{ $product->current_stock == 0 ? 'text-red-600' : ($product->current_stock < 10 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ number_format($product->current_stock, 1) }} units
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Stock Status:</p>
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
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Note: Stock levels are managed through purchases and sales. To adjust stock manually, use the stock adjustment feature on the product details page.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <x-primary-button>
                                {{ __('Update Product') }}
                            </x-primary-button>
                            <a href="{{ route('inventory.show', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                View Details
                            </a>
                        </div>
                        
                        <!-- Delete Product (if no sales/purchases) -->
                        @if($product->purchase->count() == 0 && $product->purchase->sales->count() == 0)
                            <form method="POST" action="{{ route('inventory.destroy', $product) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">
                                    {{ __('Delete Product') }}
                                </x-danger-button>
                            </form>
                        @else
                            <p class="text-xs text-gray-500">
                                Cannot delete: Product has sales or purchase history
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('purchases.create') }}?product={{ $product->id }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-center font-medium transition-colors duration-200">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Purchase
                    </a>
                    @if($product->current_stock > 0)
                        <a href="{{ route('sales.create') }}?product={{ $product->id }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md text-center font-medium transition-colors duration-200">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Record Sale
                        </a>
                    @else
                        <div class="bg-gray-300 text-gray-500 px-4 py-3 rounded-md text-center font-medium cursor-not-allowed">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Out of Stock
                        </div>
                    @endif
                    <a href="{{ route('inventory.show', $product) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-md text-center font-medium transition-colors duration-200">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View History
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-shop-layout>

