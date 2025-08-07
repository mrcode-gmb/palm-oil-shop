<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }" class="h-full bg-gray-100" x-cloak>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" />

        <!-- Tailwind -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'block' : 'hidden md:block'" class="w-64 bg-white shadow-md z-50 fixed md:relative h-full">
                <div class="p-4 text-xl font-bold text-center border-b">
                    {{ config('app.name', 'Inventory') }}
                </div>
                <nav class="mt-4 space-y-2 px-4">
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-gray-200' : '' }}">
                        Products
                    </a>
                    <a href="{{ route('purchases.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('purchases.*') ? 'bg-gray-200' : '' }}">
                        Purchases
                    </a>
                    <a href="{{ route('sales.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('sales.*') ? 'bg-gray-200' : '' }}">
                        Sales
                    </a>
                    <a href="{{ route('expenses.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('expenses.*') ? 'bg-gray-200' : '' }}">
                        Expenses
                    </a>
                    <a href="{{ route('inventory.report') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('sales/inventory') ? 'bg-gray-200' : '' }}">
                        Inventory Report
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-gray-100 text-red-600">
                            Logout
                        </button>
                    </form>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-h-screen ml-0 md:ml-64">
                <!-- Header -->
                <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center md:hidden">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="text-lg font-semibold">
                        {{ config('app.name', 'Inventory') }}
                    </div>
                </header>

                <!-- Content Area -->
                <main class="flex-1 p-4 md:p-6">
                    <!-- Flash Success Message -->
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Page Content -->
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
