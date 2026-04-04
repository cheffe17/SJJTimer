<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class CountdownTimer extends Component
{
    public string $filterType = '';

    public function setFilter(string $type): void
    {
        $this->filterType = $this->filterType === $type ? '' : $type;
    }

    public function render()
    {
        $user = auth()->user();
        $now = now();

        $baseQuery = Event::where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->couple_id) {
                $q->orWhere('couple_id', $user->couple_id);
            }
        })->with('creator');

        // Active event (happening now)
        $activeQuery = clone $baseQuery;
        if ($this->filterType) {
            $activeQuery->where('type', $this->filterType);
        }
        $activeEvent = $activeQuery
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->orderBy('start_time')
            ->first();

        // Next upcoming event
        $nextQuery = clone $baseQuery;
        if ($this->filterType) {
            $nextQuery->where('type', $this->filterType);
        }
        $nextEvent = $nextQuery
            ->where('start_time', '>', $now)
            ->orderBy('start_time')
            ->first();

        $targetEvent = $activeEvent ?? $nextEvent;

        return view('livewire.countdown-timer', [
            'activeEvent' => $activeEvent,
            'nextEvent' => $activeEvent ? null : $nextEvent,
            'targetEvent' => $targetEvent,
            'targetTimestamp' => $nextEvent?->start_time->getTimestampMs(),
            'activeEndTimestamp' => $activeEvent?->end_time?->getTimestampMs(),
        ]);
    }
}
