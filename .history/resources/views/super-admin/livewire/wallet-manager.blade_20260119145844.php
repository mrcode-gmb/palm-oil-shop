<div>
    <!-- Wallet Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Wallet Information</h3>
            @if(auth()->user()->isSuperAdmin())
                <div class="flex space-x-2">
                    <a href="{{ route('super-admin.wallets.deposit', ['business' => $wallet->business_id]) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Funds
                    </a>
                    <a href="{{ route('super-admin.wallets.withdraw', ['business' => $wallet->business_id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                        Withdraw
                    </a>
                </div>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Available Balance -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg border border-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Available Balance</p>
                        <p class="text-3xl font-bold text-blue-900">₦{{ number_format($wallet->balance, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 bg-opacity-50 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Available Balance</p>
                        <p class="text-3xl font-bold text-blue-900">₦{{ number_format($actualWalletBalance, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 bg-opacity-50 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                
                <div class="mt-4 flex items-center text-sm text-blue-600">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ strtoupper($wallet->currency) }}
                    </span>
                    <span class="ml-2">
                        {{ ucfirst($wallet->status) }} Wallet
                    </span>
                </div>
                <div class="mt-4 pt-4 border-t border-blue-100">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <p class="text-xs text-gray-500">Last Transaction</p>
                            <p class="text-sm font-medium">
                                {{ $wallet->last_transaction_at ? $wallet->last_transaction_at->diffForHumans() : 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $wallet->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($wallet->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="md:col-span-2 bg-white p-6 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-gray-600">Recent Transactions</h4>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                </div>
                <div class="space-y-4">
                    @forelse($transactions as $transaction)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full {{ $transaction->type === 'credit' ? 'bg-green-50' : 'bg-blue-50' }}">
                                    <svg class="w-5 h-5 {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-blue-600' }}" 
                                         fill="none" 
                                         stroke="currentColor" 
                                         viewBox="0 0 24 24">
                                        @if($transaction->type === 'credit')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        @endif
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $transaction->description }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-blue-600' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }}₦{{ number_format($transaction->amount, 2) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ strtoupper($transaction->type) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No transactions yet</p>
                        </div>
                    @endforelse
                </div>
                
                @if($transactions->hasPages())
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('showMessage', (message) => {
            alert(message);
        });
    });
</script>
@endpush
