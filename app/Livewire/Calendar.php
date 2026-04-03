<?php

namespace App\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use Livewire\Component;

class Calendar extends Component
{
    public string $title = '';
    public string $type = 'visit';
    public bool $shared = true;
    public string $start_time = '';
    public string $end_time = '';
    public bool $showModal = false;
    public ?int $editingEventId = null;

    public int $currentMonth;
    public int $currentYear;

    protected $rules = [
        'title' => 'required|string|max:255',
        'type' => 'required|in:flight,visit,date',
        'start_time' => 'required|date',
        'end_time' => 'nullable|date|after_or_equal:start_time',
    ];

    public function mount(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function updatedType(): void
    {
        // Flights are solo by default, visits/dates are shared
        $this->shared = $this->type !== 'flight';
    }

    public function openModal(string $date = ''): void
    {
        $this->resetForm();
        if ($date) {
            $this->start_time = $date . 'T12:00';
        }
        $this->showModal = true;
    }

    public function editEvent(int $eventId): void
    {
        $event = $this->getEvents()->firstWhere('id', $eventId);
        if (!$event) return;
        if ($event->created_by !== auth()->id()) return;

        $this->editingEventId = $eventId;
        $this->title = $event->title;
        $this->type = $event->type;
        $this->shared = $event->shared;
        $this->start_time = $event->start_time->format('Y-m-d\TH:i');
        $this->end_time = $event->end_time?->format('Y-m-d\TH:i') ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        $data = [
            'couple_id' => $user->couple_id,
            'created_by' => $user->id,
            'type' => $this->type,
            'shared' => $this->shared,
            'title' => $this->title,
            'start_time' => Carbon::parse($this->start_time)->utc(),
            'end_time' => $this->end_time ? Carbon::parse($this->end_time)->utc() : null,
            'tracking_start' => $this->type === 'visit' ? Carbon::parse($this->start_time)->utc() : null,
        ];

        if ($this->editingEventId) {
            $event = Event::where('id', $this->editingEventId)
                ->where('created_by', $user->id)
                ->first();
            $event?->update($data);
        } else {
            Event::create($data);
        }

        $this->closeModal();
        $this->dispatch('event-saved');
    }

    public function deleteEvent(int $eventId): void
    {
        Event::where('id', $eventId)
            ->where('created_by', auth()->id())
            ->delete();

        $this->dispatch('event-saved');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingEventId = null;
        $this->title = '';
        $this->type = 'visit';
        $this->shared = true;
        $this->start_time = '';
        $this->end_time = '';
        $this->resetValidation();
    }

    private function getEvents()
    {
        $user = auth()->user();

        return Event::where(function ($query) use ($user) {
            $query->where('created_by', $user->id);
            if ($user->couple_id) {
                $query->orWhere('couple_id', $user->couple_id);
            }
        })
        ->with('creator')
        ->orderBy('start_time')
        ->get();
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);

        $events = $this->getEvents();

        $startDayOfWeek = $startOfMonth->dayOfWeekIso;
        $daysInMonth = $startOfMonth->daysInMonth;

        $calendarDays = [];

        for ($i = 1; $i < $startDayOfWeek; $i++) {
            $calendarDays[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $dayEvents = $events->filter(function ($event) use ($date) {
                $start = $event->start_time->startOfDay();
                $end = $event->end_time ? $event->end_time->startOfDay() : $start;
                return $date->between($start, $end);
            });

            $calendarDays[] = [
                'day' => $day,
                'date' => $date->format('Y-m-d'),
                'isToday' => $date->isToday(),
                'events' => $dayEvents,
            ];
        }

        $upcomingEvents = $events->filter(fn ($e) => $e->start_time->isFuture())->take(5);
        $partnerName = auth()->user()->couple_id ? auth()->user()->partner()?->name : null;

        return view('livewire.calendar', [
            'calendarDays' => $calendarDays,
            'monthName' => $startOfMonth->translatedFormat('F Y'),
            'upcomingEvents' => $upcomingEvents,
            'currentUserId' => auth()->id(),
            'partnerName' => $partnerName,
        ]);
    }
}
