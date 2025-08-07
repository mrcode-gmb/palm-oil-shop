<x-shop-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record New Purchase
        </h2>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
                    @csrf

                    <!-- Product Selection -->
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                        <select id="product_id" name="product_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-unit="{{ $product->unit_type }}"
                                    data-current-stock="{{ $product->current_stock }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ ucfirst($product->unit_type) }}) - Current Stock:
                                    {{ number_format($product->current_stock, 1) }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Info Display -->
                    <div id="product-info" class="hidden bg-blue-50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Current Stock:</span>
                                <span id="current-stock" class="text-blue-600 font-semibold"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Unit Type:</span>
                                <span id="unit-type" class="text-blue-600 font-semibold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier
                                Name</label>
                            <input type="text" id="supplier_name" name="supplier_name"
                                value="{{ old('supplier_name') }}" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('supplier_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="supplier_phone" class="block text-sm font-medium text-gray-700">Supplier Phone
                                (Optional)</label>
                            <input type="tel" id="supplier_phone" name="supplier_phone"
                                value="{{ old('supplier_phone') }}"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('supplier_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Purchase Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                                value="{{ old('quantity') }}" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="buying_price_per_unit" class="block text-sm font-medium text-gray-700">Cost
                                P (₦)</label>
                            <input type="number" id="buying_price_per_unit" name="buying_price_per_unit" step="0.01"
                                min="0.01" value="{{ old('buying_price_per_unit') }}" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('buying_price_per_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase
                                Date</label>
                            <input type="date" id="purchase_date" name="purchase_date"
                                value="{{ old('purchase_date', date('Y-m-d')) }}" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('purchase_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Total Cost Display -->
                    <div id="total-display" class="hidden bg-green-50 p-4 rounded-md">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Cost</p>
                            <p id="total-cost" class="text-2xl font-bold text-green-600">₦0.00</p>
                        </div>
                        <div class="mt-2 text-center">
                            <p class="text-sm text-gray-600">New Stock Level: <span id="new-stock"
                                    class="font-semibold text-blue-600">0</span></p>
                        </div>
                    </div>

                    <!-- Unit Type -->
                    <div>
                        <label for="unit_type" class="block text-sm font-medium text-gray-700">Unit Type</label>
                        <select name="unit_type" id="unit_type" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="litre">Litre</option>
                            <option value="jerrycan">Jerrycan</option>
                        </select>
                        @error('unit_type')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                   
                    <!-- Selling Price -->
                    <div>
                        <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price
                            (₦)</label>
                        <input type="number" name="selling_price" id="selling_price" step="0.01" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        @error('selling_price')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Selling profit per unit -->
                    <div>
                        <label for="selling_profit_per_unit" class="block text-sm font-medium text-gray-700">Selling
                            Profit Per Unit (₦)</label>
                        <input type="number" name="selling_profit_per_unit" id="selling_profit_per_unit"
                            step="0.01" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        @error('selling_profit_per_unit')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Initial Stock -->
                    <div>
                        <label for="current_stock" class="block text-sm font-medium text-gray-700">Initial Stock (in
                            unit type)</label>
                        <input type="number" name="current_stock" id="current_stock" step="0.01" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        @error('current_stock')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('purchases.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200">
                            Record Purchase
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
            const costPriceInput = document.getElementById('buying_price_per_unit');
            const productInfo = document.getElementById('product-info');
            const totalDisplay = document.getElementById('total-display');
            const currentStock = document.getElementById('current-stock');
            const unitType = document.getElementById('unit-type');
            const totalCost = document.getElementById('total-cost');
            const newStock = document.getElementById('new-stock');

            function updateProductInfo() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];

                if (selectedOption.value) {
                    const stock = selectedOption.dataset.currentStock;
                    const unit = selectedOption.dataset.unit;

                    currentStock.textContent = `${parseFloat(stock).toFixed(1)} ${unit}s`;
                    unitType.textContent = unit;

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
                const costPrice = parseFloat(costPriceInput.value) || 0;

                if (selectedOption.value && quantity > 0 && costPrice > 0) {
                    const total = quantity * costPrice;
                    const currentStockValue = parseFloat(selectedOption.dataset.currentStock) || 0;
                    const newStockValue = currentStockValue + quantity;

                    totalCost.textContent = `₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                    newStock.textContent = `${newStockValue.toFixed(1)} units`;
                    totalDisplay.classList.remove('hidden');
                } else {
                    totalDisplay.classList.add('hidden');
                }
            }

            productSelect.addEventListener('change', updateProductInfo);
            quantityInput.addEventListener('input', calculateTotal);
            costPriceInput.addEventListener('input', calculateTotal);

            // Initialize if product is pre-selected
            if (productSelect.value) {
                updateProductInfo();
            }
        });
    </script>
</x-shop-layout>
