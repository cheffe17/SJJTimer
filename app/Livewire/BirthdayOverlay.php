<?php

namespace App\Livewire;

use Livewire\Component;

class BirthdayOverlay extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user && $user->email === 'anita.kotelko@gmail.com') {
            $this->show = true;
        }
    }

    public function dismiss(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.birthday-overlay');
    }
}
