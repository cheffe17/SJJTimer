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

    private function baseQuery()
    {
        $user = auth()->user();

        return Event::where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->couple_id) {
                $q->orWhere('couple_id', $user->couple_id);
            }
        })->with('creator');
    }

    public function render()
    {
        $now = now();

        // Active event (happening now)
        $activeQuery = $this->baseQuery();
        if ($this->filterType) {
            $activeQuery->where('type', $this->filterType);
        }
        $activeEvent = $activeQuery
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->orderBy('start_time')
            ->first();

        $isStayActive = $activeEvent && $activeEvent->isVisitWithFlights();

        // Next upcoming event — fresh query
        $nextQuery = $this->baseQuery();
        if ($this->filterType) {
            $nextQuery->where('type', $this->filterType);
        }
        $nextEvent = $nextQuery
            ->where('start_time', '>', $now)
            ->whereNull('parent_event_id')
            ->orderBy('start_time')
            ->first();

        $targetEvent = $activeEvent ?? $nextEvent;

        // Timestamps for client-side countdown (all in ms)
        $activeEndTimestamp = null;
        $activeStartTimestamp = null;
        $location = null;

        if ($activeEvent) {
            $activeStartTimestamp = $activeEvent->start_time->getTimestampMs();
            $location = $activeEvent->location;
            if ($isStayActive && $activeEvent->return_time) {
                $activeEndTimestamp = $activeEvent->return_time->getTimestampMs();
            } else {
                $activeEndTimestamp = $activeEvent->end_time?->getTimestampMs();
            }
        }

        return view('livewire.countdown-timer', [
            'activeEvent' => $activeEvent,
            'nextEvent' => $activeEvent ? null : $nextEvent,
            'targetEvent' => $targetEvent,
            'targetTimestamp' => $nextEvent && !$activeEvent ? $nextEvent->start_time->getTimestampMs() : null,
            'activeEndTimestamp' => $activeEndTimestamp,
            'activeStartTimestamp' => $activeStartTimestamp,
            'isStayActive' => $isStayActive,
            'location' => $location,
            'nextEventTitle' => ($nextEvent && !$activeEvent) ? $nextEvent->title : null,
            'nextEventType' => ($nextEvent && !$activeEvent) ? $nextEvent->type : null,
            'nextEventLocation' => ($nextEvent && !$activeEvent) ? $nextEvent->location : null,
            'nextEventDate' => ($nextEvent && !$activeEvent) ? $nextEvent->start_time->format('d.m.Y, H:i') : null,
            'nextEventCreator' => ($nextEvent && !$activeEvent) ? $nextEvent->creator->name : null,
            'activeEventTitle' => $activeEvent?->title,
            'activeEventType' => $activeEvent?->type,
            'activeEventDate' => $activeEvent?->start_time->format('d.m.Y, H:i'),
            'activeEventEndDate' => $isStayActive && $activeEvent?->return_time
                ? $activeEvent->return_time->format('d.m.Y, H:i')
                : $activeEvent?->end_time?->format('H:i'),
        ]);
    }
}
