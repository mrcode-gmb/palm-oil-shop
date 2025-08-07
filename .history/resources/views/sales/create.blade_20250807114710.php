<x-shop-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record New Sale
        </h2>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('sales.store') }}" class="space-y-6">
                    @csrf

                    <!-- Product Selection -->
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                        <select id="product_id" name="product_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-price="{{ $product->purchase_price }}" 
                                        data-stock="{{ $product->quantity }}"
                                        data-unit="{{ $product->product->unit_type }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->product->name }} - current stock ({{ number_format($product->quantity, 1) }} {{ $product->unit_type }}s available) - ₦{{ number_format($product->purchase_price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Info Display -->
                    <div id="product-info" class="hidden bg-blue-50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Available Stock:</span>
                                <span id="available-stock" class="text-blue-600 font-semibold"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Unit Type:</span>
                                <span id="unit-type" class="text-blue-600 font-semibold"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Purchase Price:</span>
                                <span id="selling-price" class="text-blue-600 font-semibold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price (₦)</label>
                            <input type="number" 
                                id="selling_price" 
                                name="selling_price" 
                                step="0.01" 
                                min="0.01" 
                                value="{{ old('selling_price') }}" 
                                required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('selling_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Enter the selling price per quantity</p>
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" 
                                id="quantity" 
                                name="quantity" 
                                step="0.01" 
                                min="0.01" 
                                value="{{ old('quantity') }}" 
                                required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Enter the quantity to sell</p>
                        </div>
                    </div>
                    <!-- Total Amount Display -->
                    <div id="total-display" class="hidden bg-green-50 p-4 rounded-md">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p id="total-amount" class="text-2xl font-bold text-green-600">₦0.00</p>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name (Optional)</label>
                            <input type="text" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   value="{{ old('customer_name') }}" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone (Optional)</label>
                            <input type="tel" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone') }}" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('sales.dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200">
                            Record Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for dynamic calculations -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const productInfo = document.getElementById('product-info');
            const totalDisplay = document.getElementById('total-display');
            const availableStock = document.getElementById('available-stock');
            const unitType = document.getElementById('unit-type');
            const sellingPrice = document.getElementById('selling-price');
            const totalAmount = document.getElementById('total-amount');
            const sellingPriceValue = document.getElementById('selling_price');

            function updateProductInfo() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                
                if (selectedOption.value) {
                    const stock = selectedOption.dataset.stock;
                    const unit = selectedOption.dataset.unit;
                    const price = selectedOption.dataset.price;
                    
                    availableStock.textContent = `${parseFloat(stock).toFixed(1)} ${unit}s`;
                    unitType.textContent = unit;
                    sellingPrice.textContent = `₦${parseFloat(price).toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                    
                    productInfo.classList.remove('hidden');
                    calculateTotal();
                } else {
                    productInfo.classList.add('hidden');
                    totalDisplay.classList.add('hidden');
                }
            }

            function calculateTotal() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const quantity = parseFloat(quantityInput.value) || 0;
                
                if (selectedOption.value && quantity > 0) {
                    const price = parseFloat(selectedOption.dataset.price);
                    const total = sellingPriceValue.val * quantity;
                    
                    totalAmount.textContent = `₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                    totalDisplay.classList.remove('hidden');
                } else {
                    totalDisplay.classList.add('hidden');
                }
            }

            function validateStock() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const quantity = parseFloat(quantityInput.value) || 0;
                
                if (selectedOption.value && quantity > 0) {
                    const stock = parseFloat(selectedOption.dataset.stock);
                    
                    if (quantity > stock) {
                        quantityInput.setCustomValidity(`Only ${stock} units available in stock`);
                    } else {
                        quantityInput.setCustomValidity('');
                    }
                }
            }

            productSelect.addEventListener('change', updateProductInfo);
            quantityInput.addEventListener('input', function() {
                calculateTotal();
                validateStock();
            });

            // Initialize if product is pre-selected
            if (productSelect.value) {
                updateProductInfo();
            }
        });
    </script>
</x-shop-layout>

