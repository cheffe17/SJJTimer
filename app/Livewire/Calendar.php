<?php

namespace App\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
    public int $currentWeek;
    public int $currentDay;
    public string $view = 'month'; // year, month, week, day

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
        $this->currentWeek = now()->weekOfYear;
        $this->currentDay = now()->day;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
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

    public function previousPeriod(): void
    {
        match ($this->view) {
            'year' => $this->currentYear--,
            'month' => $this->previousMonth(),
            'week' => $this->shiftWeek(-1),
            'day' => $this->shiftDay(-1),
        };
    }

    public function nextPeriod(): void
    {
        match ($this->view) {
            'year' => $this->currentYear++,
            'month' => $this->nextMonth(),
            'week' => $this->shiftWeek(1),
            'day' => $this->shiftDay(1),
        };
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->currentWeek = now()->weekOfYear;
        $this->currentDay = now()->day;
    }

    private function shiftWeek(int $direction): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay)
            ->addWeeks($direction);
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->currentDay = $date->day;
        $this->currentWeek = $date->weekOfYear;
    }

    private function shiftDay(int $direction): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay)
            ->addDays($direction);
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->currentDay = $date->day;
    }

    public function updatedType(): void
    {
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

        $startTime = Carbon::parse($this->start_time)->utc();
        $endTime = $this->end_time
            ? Carbon::parse($this->end_time)->utc()
            : $startTime->copy()->addHours(3);

        $data = [
            'couple_id' => $user->couple_id,
            'created_by' => $user->id,
            'type' => $this->type,
            'shared' => $this->shared,
            'title' => $this->title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'tracking_start' => $this->type === 'visit' ? $startTime : null,
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

    private function getWeekDays(): array
    {
        $baseDate = Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay);
        $startOfWeek = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
        $events = $this->getEvents();
        $hours = range(0, 23);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayEvents = $events->filter(function ($event) use ($date) {
                $start = $event->start_time->startOfDay();
                $end = $event->end_time ? $event->end_time->startOfDay() : $start;
                return $date->between($start, $end);
            });

            $days[] = [
                'date' => $date,
                'label' => $date->translatedFormat('D d.m.'),
                'isToday' => $date->isToday(),
                'events' => $dayEvents,
            ];
        }

        return ['days' => $days, 'hours' => $hours];
    }

    private function getDayEvents(): array
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay);
        $events = $this->getEvents();

        $dayEvents = $events->filter(function ($event) use ($date) {
            $start = $event->start_time->startOfDay();
            $end = $event->end_time ? $event->end_time->startOfDay() : $start;
            return $date->between($start, $end);
        });

        return [
            'date' => $date,
            'label' => $date->translatedFormat('l, d. F Y'),
            'events' => $dayEvents,
            'hours' => range(0, 23),
        ];
    }

    private function getYearMonths(): array
    {
        $events = $this->getEvents();
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $startOfMonth = Carbon::create($this->currentYear, $m, 1);
            $daysInMonth = $startOfMonth->daysInMonth;
            $startDow = $startOfMonth->dayOfWeekIso;
            $days = [];

            for ($i = 1; $i < $startDow; $i++) {
                $days[] = null;
            }

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::create($this->currentYear, $m, $d);
                $hasEvents = $events->contains(function ($event) use ($date) {
                    $start = $event->start_time->startOfDay();
                    $end = $event->end_time ? $event->end_time->startOfDay() : $start;
                    return $date->between($start, $end);
                });

                $days[] = [
                    'day' => $d,
                    'date' => $date->format('Y-m-d'),
                    'isToday' => $date->isToday(),
                    'hasEvents' => $hasEvents,
                ];
            }

            $months[] = [
                'name' => $startOfMonth->translatedFormat('F'),
                'days' => $days,
            ];
        }

        return $months;
    }

    public function render()
    {
        $events = $this->getEvents();
        $partnerName = auth()->user()->couple_id ? auth()->user()->partner()?->name : null;
        $currentUserId = auth()->id();

        // Active events (happening right now)
        $now = now();
        $activeEvents = $events->filter(function ($event) use ($now) {
            return $event->start_time->lte($now) && $event->end_time && $event->end_time->gte($now);
        });

        // Upcoming events
        $upcomingEvents = $events->filter(fn ($e) => $e->start_time->isFuture())->take(5);

        // View-specific data
        $viewData = match ($this->view) {
            'year' => ['yearMonths' => $this->getYearMonths()],
            'week' => ['weekData' => $this->getWeekDays()],
            'day' => ['dayData' => $this->getDayEvents()],
            default => ['calendarDays' => $this->buildMonthDays($events)],
        };

        $periodLabel = match ($this->view) {
            'year' => (string) $this->currentYear,
            'month' => Carbon::create($this->currentYear, $this->currentMonth, 1)->translatedFormat('F Y'),
            'week' => 'KW ' . $this->currentWeek . ' · ' . $this->currentYear,
            'day' => Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay)->translatedFormat('d. F Y'),
        };

        return view('livewire.calendar', array_merge($viewData, [
            'periodLabel' => $periodLabel,
            'upcomingEvents' => $upcomingEvents,
            'activeEvents' => $activeEvents,
            'currentUserId' => $currentUserId,
            'partnerName' => $partnerName,
        ]));
    }

    private function buildMonthDays($events): array
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
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

        return $calendarDays;
    }
}
