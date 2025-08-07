@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Products</h1>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    + Add Product
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2">{{ $product->name }}</h2>
                        <p class="text-gray-700 mb-4">Price: ₦{{ number_format($product->price, 2) }}</p>
                        <p class="text-gray-700 mb-4">Current Stock: {{ $product->current_stock }}</p>

                        <div class="flex flex-wrap gap-2">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('products.edit', $product->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Edit Product
                                </a>

                                <a href="{{ route('purchases.create') }}?product={{ $product->id }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Add Purchase
                                </a>

                                @if($product->current_stock > 0)
                                    <a href="{{ route('sales.create') }}?product={{ $product->id }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                        Record Sale
                                    </a>
                                @else
                                    <div class="bg-gray-300 text-gray-500 px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed">
                                        Out of Stock
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
