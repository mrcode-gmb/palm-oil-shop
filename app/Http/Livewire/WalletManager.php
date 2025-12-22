<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Wallet;
use Livewire\WithPagination;

class WalletManager extends Component
{
    use WithPagination;

    public $wallet;
    public function mount(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    public function render()
    {
        $transactions = $this->wallet->transactions()
            ->latest()
            ->paginate(5);
            
        return view('super-admin.livewire.wallet-manager', [
            'transactions' => $transactions
        ]);
    }
}
