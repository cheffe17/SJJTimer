<?php

namespace App\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use Livewire\Component;

class Calendar extends Component
{
    // Form fields
    public string $title = '';
    public string $type = 'visit';
    public bool $shared = true;
    public string $start_time = '';
    public string $end_time = '';
    public bool $showModal = false;
    public ?int $editingEventId = null;

    // Flight + Stay logic
    public string $outbound_flight_time = '';
    public string $return_flight_time = '';

    // Recurring fields
    public string $recurrence_rule = '';
    public string $recurrence_day = '';
    public string $recurrence_time = '';
    public string $recurrence_until = '';

    // Calendar navigation
    public int $currentMonth;
    public int $currentYear;
    public int $currentWeek;
    public int $currentDay;
    public string $view = 'month';

    protected $rules = [
        'title' => 'required|string|max:255',
        'type' => 'required|in:visit,virtual_date,live_date,anniversary',
        'start_time' => 'required|date',
        'end_time' => 'nullable|date|after_or_equal:start_time',
        'outbound_flight_time' => 'nullable|date',
        'return_flight_time' => 'nullable|date|after_or_equal:outbound_flight_time',
        'recurrence_rule' => 'nullable|in:weekly,biweekly,monthly,yearly',
        'recurrence_day' => 'nullable|string',
        'recurrence_time' => 'nullable|string',
        'recurrence_until' => 'nullable|date',
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
        // Visits are shared by default, others too
        $this->shared = true;
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

        // Flight logic
        if ($event->type === Event::TYPE_VISIT && $event->return_time) {
            $this->outbound_flight_time = $event->start_time->format('Y-m-d\TH:i');
            $this->return_flight_time = $event->return_time->format('Y-m-d\TH:i');
        }

        // Recurring
        $this->recurrence_rule = $event->recurrence_rule ?? '';
        $this->recurrence_day = $event->recurrence_day ?? '';
        $this->recurrence_time = $event->recurrence_time ? substr($event->recurrence_time, 0, 5) : '';
        $this->recurrence_until = $event->recurrence_until?->format('Y-m-d') ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        // If visit with flight data, use outbound as start_time for validation
        if ($this->type === Event::TYPE_VISIT && $this->outbound_flight_time && $this->return_flight_time) {
            $this->start_time = $this->outbound_flight_time;
            $this->end_time = $this->return_flight_time;
        }

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
            'tracking_start' => $this->type === Event::TYPE_VISIT ? $startTime : null,
            'recurrence_rule' => $this->recurrence_rule ?: null,
            'recurrence_day' => $this->recurrence_day ?: null,
            'recurrence_time' => $this->recurrence_time ?: null,
            'recurrence_until' => $this->recurrence_until ?: null,
        ];

        // Visit with flight logic: outbound + return flights
        if ($this->type === Event::TYPE_VISIT && $this->outbound_flight_time && $this->return_flight_time) {
            $outbound = Carbon::parse($this->outbound_flight_time)->utc();
            $return = Carbon::parse($this->return_flight_time)->utc();

            $data['start_time'] = $outbound;
            $data['end_time'] = $return;
            $data['return_time'] = $return;
            $data['tracking_start'] = $outbound;
        }

        if ($this->editingEventId) {
            $event = Event::where('id', $this->editingEventId)
                ->where('created_by', $user->id)
                ->first();
            if ($event) {
                $event->update($data);
                // Delete old child events (auto-generated flights)
                $event->childEvents()->delete();
            }
        } else {
            $event = Event::create($data);
        }

        // Auto-create child flight events for visit with flights
        if ($event && $this->type === Event::TYPE_VISIT && $this->outbound_flight_time && $this->return_flight_time) {
            $outbound = Carbon::parse($this->outbound_flight_time)->utc();
            $return = Carbon::parse($this->return_flight_time)->utc();

            // Hinflug (3h duration)
            Event::create([
                'couple_id' => $user->couple_id,
                'created_by' => $user->id,
                'parent_event_id' => $event->id,
                'type' => Event::TYPE_VISIT,
                'shared' => true,
                'title' => '✈ Hinflug: ' . $this->title,
                'start_time' => $outbound,
                'end_time' => $outbound->copy()->addHours(3),
            ]);

            // Rückflug (3h duration)
            Event::create([
                'couple_id' => $user->couple_id,
                'created_by' => $user->id,
                'parent_event_id' => $event->id,
                'type' => Event::TYPE_VISIT,
                'shared' => true,
                'title' => '✈ Rückflug: ' . $this->title,
                'start_time' => $return,
                'end_time' => $return->copy()->addHours(3),
            ]);
        }

        $this->closeModal();
        $this->dispatch('event-saved');
    }

    public function deleteEvent(int $eventId): void
    {
        // Cascade: child events deleted via DB constraint
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
        $this->outbound_flight_time = '';
        $this->return_flight_time = '';
        $this->recurrence_rule = '';
        $this->recurrence_day = '';
        $this->recurrence_time = '';
        $this->recurrence_until = '';
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

    /**
     * Get all events including recurring occurrences for a date range.
     */
    private function getEventsWithRecurrences(Carbon $from, Carbon $to)
    {
        $events = $this->getEvents();
        $allEvents = collect();

        foreach ($events as $event) {
            $allEvents->push($event);

            if ($event->isRecurring()) {
                $occurrences = $event->generateOccurrences($from, $to);
                foreach ($occurrences as $occurrence) {
                    $allEvents->push($occurrence);
                }
            }
        }

        return $allEvents->sortBy('start_time');
    }

    private function getWeekDays(): array
    {
        $baseDate = Carbon::create($this->currentYear, $this->currentMonth, $this->currentDay);
        $startOfWeek = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
        $events = $this->getEventsWithRecurrences($startOfWeek, $endOfWeek);
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
        $events = $this->getEventsWithRecurrences($date->copy()->startOfDay(), $date->copy()->endOfDay());

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
        $from = Carbon::create($this->currentYear, 1, 1);
        $to = Carbon::create($this->currentYear, 12, 31);
        $events = $this->getEventsWithRecurrences($from, $to);
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

        // For month view, include recurring occurrences
        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $allEvents = $this->getEventsWithRecurrences($monthStart, $monthEnd);

        // Active events (happening right now)
        $activeEvents = $allEvents->filter(fn ($event) => $event->isActive());

        // Upcoming events (non-recurring base events only)
        $upcomingEvents = $events->filter(fn ($e) => $e->start_time->isFuture())->take(5);

        // View-specific data
        $viewData = match ($this->view) {
            'year' => ['yearMonths' => $this->getYearMonths()],
            'week' => ['weekData' => $this->getWeekDays()],
            'day' => ['dayData' => $this->getDayEvents()],
            default => ['calendarDays' => $this->buildMonthDays($allEvents)],
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
