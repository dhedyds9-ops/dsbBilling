<?php

namespace App\Livewire\CustomerPortal;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Notification\Notification;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.customer-app')]
class Info extends Component
{
    use WithPagination;

    public function render()
    {
        $notifications = Notification::where('recipient_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.customer-portal.info', [
            'notifications' => $notifications
        ]);
    }
}
