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

                    @if(auth()->user()->isAdmin())
                        <!-- Admin: Product Selection from Inventory -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Product (From Inventory)</label>
                            <select id="product_id" name="product_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-price="{{ $product->purchase_price }}" 
                                            data-stock="{{ $product->quantity }}"
                                            data-unit="{{ $product->product->unit_type }}"
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->product->name }} - Stock: {{ number_format($product->quantity, 1) }} - Cost: ₦{{ number_format($product->purchase_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <!-- Staff: Assignment Selection -->
                        <div>
                            <label for="assignment_id" class="block text-sm font-medium text-gray-700">Select from Your Assigned Products</label>
                            <select id="assignment_id" name="assignment_id" required onchange="updateAssignmentInfo()" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select an assignment</option>
                                @foreach($assignments as $assignment)
                                    @if($assignment->remaining_quantity > 0)
                                        <option value="{{ $assignment->id }}" 
                                                data-product-id="{{ $assignment->purchase_id }}"
                                                data-price="{{ $assignment->expected_selling_price }}" 
                                                data-stock="{{ $assignment->remaining_quantity }}"
                                                data-cost="{{ $assignment->purchase->cost_price_per_unit }}"
                                                data-unit="{{ $assignment->purchase->product->unit_type }}"
                                                {{ (old('assignment_id') == $assignment->id || request('assignment_id') == $assignment->id) ? 'selected' : '' }}>
                                            {{ $assignment->purchase->product->name }} - Available: {{ $assignment->remaining_quantity }} - Price: ₦{{ number_format($assignment->expected_selling_price, 2) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" id="product_id" name="product_id" value="{{ old('product_id') }}">
                            @error('assignment_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

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

                    <!-- Payment Type -->
                    <div>
                        <label for="payment_type" class="block text-sm font-medium text-gray-700">Payment Type</label>
                        <select id="payment_type" name="payment_type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select payment method</option>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ old('payment_type') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="pos" {{ old('payment_type') == 'pos' ? 'selected' : '' }}>POS</option>
                            <option value="mobile_money" {{ old('payment_type') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('payment_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Creditor Selection (for Credit sales) -->
                    <div id="creditor-section" class="hidden">
                        <label for="creditor_id" class="block text-sm font-medium text-gray-700">Select Creditor</label>
                        <select id="creditor_id" name="creditor_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a creditor</option>
                            @foreach($creditors as $creditor)
                                <option value="{{ $creditor->id }}">{{ $creditor->name }}</option>
                            @endforeach
                        </select>
                        @error('creditor_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="amount-paid-section" class="hidden">
                        <label for="amount_paid" class="block text-sm font-medium text-gray-700">Amount Paid (₦)</label>
                        <input type="number" id="amount_paid" name="amount_paid" step="0.01" min="0" value="{{ old('amount_paid', 0) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @error('amount_paid')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                    const total = sellingPriceValue.value * quantity;
                    
                    totalAmount.textContent = `₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                    totalDisplay.classList.remove('hidden');
                } else {
                    totalDisplay.classList.add('hidden');
                }
            }
            function calculateTotalAmount() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const quantity = parseFloat(quantityInput.value) || 0;
                
                if (selectedOption.value && quantity > 0) {
                    const price = parseFloat(selectedOption.dataset.price);
                    const total = sellingPriceValue.value * quantity;
                    
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
                calculateTotalAmount();
            });

            sellingPriceValue.addEventListener('input', function() {
                calculateTotal();
                validateStock();
                calculateTotalAmount();
            });
            // Initialize if product is pre-selected
            if (productSelect.value) {
                updateProductInfo();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeSelect = document.getElementById('payment_type');
            const creditorSection = document.getElementById('creditor-section');
            const creditorSelect = document.getElementById('creditor_id');
            const amountPaidSection = document.getElementById('amount-paid-section');

            function toggleCreditorSection() {
                if (paymentTypeSelect.value === 'credit') {
                    creditorSection.classList.remove('hidden');
                    amountPaidSection.classList.remove('hidden');
                    creditorSelect.required = true;
                } else {
                    creditorSection.classList.add('hidden');
                    amountPaidSection.classList.add('hidden');
                    creditorSelect.required = false;
                }
            }

            paymentTypeSelect.addEventListener('change', toggleCreditorSection);
            toggleCreditorSection(); // Initial check
        });

        // Function for staff assignment selection
        function updateAssignmentInfo() {
            const assignmentSelect = document.getElementById('assignment_id');
            const productIdInput = document.getElementById('product_id');
            const sellingPriceInput = document.getElementById('selling_price');
            const productInfo = document.getElementById('product-info');
            const availableStock = document.getElementById('available-stock');
            const unitType = document.getElementById('unit-type');
            const sellingPrice = document.getElementById('selling-price');
            
            const selectedOption = assignmentSelect.options[assignmentSelect.selectedIndex];
            
            if (selectedOption.value) {
                const productId = selectedOption.dataset.productId;
                const price = selectedOption.dataset.price;
                const stock = selectedOption.dataset.stock;
                const unit = selectedOption.dataset.unit;
                
                // Update hidden product ID field
                productIdInput.value = productId;
                
                // Update selling price
                sellingPriceInput.value = price;
                
                // Update product info display
                availableStock.textContent = `${parseFloat(stock).toFixed(1)} ${unit}s`;
                unitType.textContent = unit;
                sellingPrice.textContent = `₦${parseFloat(price).toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                
                productInfo.classList.remove('hidden');
            } else {
                productIdInput.value = '';
                sellingPriceInput.value = '';
                productInfo.classList.add('hidden');
            }
        }

        // Initialize assignment info if pre-selected
        document.addEventListener('DOMContentLoaded', function() {
            const assignmentSelect = document.getElementById('assignment_id');
            if (assignmentSelect && assignmentSelect.value) {
                updateAssignmentInfo();
            }
        });
    </script>
</x-shop-layout>

