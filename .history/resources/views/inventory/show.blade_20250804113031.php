@if(auth()->user()->isAdmin())
    <a href="{{ route('products.edit', $product->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
        Edit Product
    </a>

    <a href="{{ route('purchases.create') }}?product={{ $product->id }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
        Add Purchase
    </a>
@endif

@if($product->current_stock > 0)
    <a href="{{ route('sales.create') }}?product={{ $product->id }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
        Record Sale
    </a>
@else
    <div class="bg-gray-300 text-gray-500 px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed">
        Out of Stock
    </div>
@endif
