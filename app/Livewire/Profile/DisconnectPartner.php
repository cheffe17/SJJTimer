<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DisconnectPartner extends Component
{
    public bool $confirmed = false;

    public function disconnect(): void
    {
        $user = auth()->user();
        if (!$user->couple_id) return;

        $couple = $user->couple;

        DB::transaction(function () use ($couple) {
            $couple->user1->update(['couple_id' => null]);
            $couple->user2->update(['couple_id' => null]);
            $couple->delete();
        });

        $this->redirect(route('profile'), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        $partner = $user->couple_id ? $user->partner() : null;

        return view('livewire.profile.disconnect-partner', [
            'isConnected' => (bool) $user->couple_id,
            'partner' => $partner,
        ]);
    }
}
