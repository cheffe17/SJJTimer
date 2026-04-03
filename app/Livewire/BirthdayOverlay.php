<?php

namespace App\Livewire;

use Livewire\Component;

class BirthdayOverlay extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user
            && $user->email === 'anita.kotelko@gmail.com'
            && !$user->has_seen_birthday
        ) {
            $this->show = true;
        }
    }

    public function dismiss(): void
    {
        $user = auth()->user();
        $user->update(['has_seen_birthday' => true]);
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.birthday-overlay');
    }
}
