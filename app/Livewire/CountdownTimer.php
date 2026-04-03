<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class CountdownTimer extends Component
{
    public function render()
    {
        $user = auth()->user();

        $nextEvent = Event::where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->couple_id) {
                $q->orWhere('couple_id', $user->couple_id);
            }
        })
        ->where('start_time', '>', now())
        ->orderBy('start_time')
        ->with('creator')
        ->first();

        return view('livewire.countdown-timer', [
            'nextEvent' => $nextEvent,
            'targetTimestamp' => $nextEvent?->start_time->getTimestampMs(),
        ]);
    }
}
