<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    const TYPE_VISIT = 'visit';
    const TYPE_VIRTUAL_DATE = 'virtual_date';
    const TYPE_LIVE_DATE = 'live_date';
    const TYPE_ANNIVERSARY = 'anniversary';

    const TYPES = [
        self::TYPE_VISIT,
        self::TYPE_VIRTUAL_DATE,
        self::TYPE_LIVE_DATE,
        self::TYPE_ANNIVERSARY,
    ];

    const RECURRENCE_RULES = ['weekly', 'biweekly', 'monthly', 'yearly'];

    const DAY_MAP = [
        'monday' => Carbon::MONDAY,
        'tuesday' => Carbon::TUESDAY,
        'wednesday' => Carbon::WEDNESDAY,
        'thursday' => Carbon::THURSDAY,
        'friday' => Carbon::FRIDAY,
        'saturday' => Carbon::SATURDAY,
        'sunday' => Carbon::SUNDAY,
    ];

    protected $fillable = [
        'couple_id',
        'created_by',
        'parent_event_id',
        'type',
        'shared',
        'title',
        'start_time',
        'end_time',
        'return_time',
        'tracking_start',
        'recurrence_rule',
        'recurrence_day',
        'recurrence_time',
        'recurrence_until',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'return_time' => 'datetime',
            'tracking_start' => 'datetime',
            'recurrence_until' => 'date',
            'shared' => 'boolean',
        ];
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function childEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function isRecurring(): bool
    {
        return !empty($this->recurrence_rule);
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->start_time->lte($now) && $this->end_time && $this->end_time->gte($now);
    }

    public function isVisitWithFlights(): bool
    {
        return $this->type === self::TYPE_VISIT && $this->return_time !== null;
    }

    /**
     * Generate recurring event occurrences as virtual Event instances.
     *
     * Google Calendar principle:
     * - The FIRST occurrence is always at the original start_time (even if it's on a different weekday)
     * - All SUBSEQUENT occurrences follow the recurrence rule strictly
     *   (e.g., every Monday if recurrence_day = 'monday')
     *
     * The original event (start_time) is NOT duplicated here — it's already in the base events.
     * This method only generates the additional occurrences beyond the first one.
     */
    public function generateOccurrences(Carbon $from, Carbon $to): array
    {
        if (!$this->isRecurring()) {
            return [];
        }

        $occurrences = [];
        $duration = $this->end_time
            ? $this->start_time->diffInMinutes($this->end_time)
            : 180; // default 3h

        $until = $this->recurrence_until
            ? Carbon::parse($this->recurrence_until)->endOfDay()
            : $to;
        $until = $until->lt($to) ? $until : $to;

        // Determine the time for recurring events
        $recurTime = $this->recurrence_time
            ? Carbon::parse($this->recurrence_time)
            : $this->start_time;
        $hour = $recurTime->hour;
        $minute = $recurTime->minute;

        // Calculate the first recurring date AFTER the original start_time
        $firstRecurring = $this->getFirstRecurringDate($hour, $minute);

        if (!$firstRecurring || $firstRecurring->gt($until)) {
            return [];
        }

        $current = $firstRecurring->copy();

        while ($current->lte($until)) {
            if ($current->gte($from)) {
                $occurrence = new self($this->toArray());
                $occurrence->id = $this->id;
                $occurrence->start_time = $current->copy();
                $occurrence->end_time = $current->copy()->addMinutes($duration);
                $occurrence->exists = false; // mark as virtual
                $occurrence->setRelation('creator', $this->creator);
                $occurrences[] = $occurrence;
            }

            $current = $this->advanceByRule($current);
        }

        return $occurrences;
    }

    /**
     * Get the first recurring date after the original start_time.
     */
    private function getFirstRecurringDate(int $hour, int $minute): ?Carbon
    {
        $start = $this->start_time->copy();

        return match ($this->recurrence_rule) {
            'weekly', 'biweekly' => $this->getFirstWeeklyDate($start, $hour, $minute),
            'monthly' => $start->copy()->addMonth()->setTime($hour, $minute, 0),
            'yearly' => $start->copy()->addYear()->setTime($hour, $minute, 0),
            default => null,
        };
    }

    /**
     * For weekly/biweekly: find the next occurrence on the configured weekday.
     */
    private function getFirstWeeklyDate(Carbon $start, int $hour, int $minute): Carbon
    {
        $targetDow = $this->recurrence_day
            ? (self::DAY_MAP[$this->recurrence_day] ?? Carbon::MONDAY)
            : $start->dayOfWeekIso;

        // Find the next target weekday after start
        $next = $start->copy()->next(match ($targetDow) {
            Carbon::MONDAY => Carbon::MONDAY,
            Carbon::TUESDAY => Carbon::TUESDAY,
            Carbon::WEDNESDAY => Carbon::WEDNESDAY,
            Carbon::THURSDAY => Carbon::THURSDAY,
            Carbon::FRIDAY => Carbon::FRIDAY,
            Carbon::SATURDAY => Carbon::SATURDAY,
            Carbon::SUNDAY => Carbon::SUNDAY,
        });
        $next->setTime($hour, $minute, 0);

        // For biweekly, add another week if the gap is only 1 week
        // Actually: biweekly means every 2 weeks from first occurrence
        // The first biweekly occurrence is 2 weeks after the next target day
        // No — first occurrence is the very next target weekday, then skip every 2 weeks
        if ($this->recurrence_rule === 'biweekly') {
            $next->addWeek();
        }

        return $next;
    }

    /**
     * Advance a date by the recurrence rule.
     */
    private function advanceByRule(Carbon $current): Carbon
    {
        return match ($this->recurrence_rule) {
            'weekly' => $current->addWeek(),
            'biweekly' => $current->addWeeks(2),
            'monthly' => $current->addMonth(),
            'yearly' => $current->addYear(),
            default => $current->addWeek(),
        };
    }

    /**
     * Convert to FullCalendar-compatible event format.
     */
    public function toFullCalendarEvent(): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start_time->toIso8601String(),
            'end' => $this->end_time?->toIso8601String(),
            'color' => $this->getFullCalendarColor(),
            'extendedProps' => [
                'type' => $this->type,
                'shared' => $this->shared,
                'created_by' => $this->created_by,
                'creator_name' => $this->creator?->name,
            ],
        ];

        // Add recurrence rule for FullCalendar RRule plugin
        if ($this->isRecurring()) {
            $rrule = $this->toRRuleString();
            if ($rrule) {
                $data['rrule'] = $rrule;
                $data['duration'] = $this->end_time
                    ? gmdate('H:i', $this->start_time->diffInSeconds($this->end_time))
                    : '03:00';
            }
        }

        return $data;
    }

    /**
     * Generate an RFC 5545 RRULE string for FullCalendar.
     */
    public function toRRuleString(): ?string
    {
        if (!$this->isRecurring()) {
            return null;
        }

        $freq = match ($this->recurrence_rule) {
            'weekly' => 'WEEKLY',
            'biweekly' => 'WEEKLY',
            'monthly' => 'MONTHLY',
            'yearly' => 'YEARLY',
            default => null,
        };

        if (!$freq) return null;

        $parts = ["FREQ={$freq}"];

        if ($this->recurrence_rule === 'biweekly') {
            $parts[] = 'INTERVAL=2';
        }

        if ($this->recurrence_day && in_array($this->recurrence_rule, ['weekly', 'biweekly'])) {
            $dayMap = [
                'monday' => 'MO', 'tuesday' => 'TU', 'wednesday' => 'WE',
                'thursday' => 'TH', 'friday' => 'FR', 'saturday' => 'SA', 'sunday' => 'SU',
            ];
            if (isset($dayMap[$this->recurrence_day])) {
                $parts[] = 'BYDAY=' . $dayMap[$this->recurrence_day];
            }
        }

        if ($this->recurrence_until) {
            $parts[] = 'UNTIL=' . Carbon::parse($this->recurrence_until)->format('Ymd\THis\Z');
        }

        return implode(';', $parts);
    }

    private function getFullCalendarColor(): string
    {
        return match ($this->type) {
            self::TYPE_VISIT => '#0ea5e9',       // sky-500
            self::TYPE_VIRTUAL_DATE => '#8b5cf6', // violet-500
            self::TYPE_LIVE_DATE => '#f43f5e',    // rose-500
            self::TYPE_ANNIVERSARY => '#f59e0b',  // amber-500
            default => '#6b7280',
        };
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_VISIT => 'Besuch',
            self::TYPE_VIRTUAL_DATE => 'Virtuelles Date',
            self::TYPE_LIVE_DATE => 'Live Date',
            self::TYPE_ANNIVERSARY => 'Jahrestag',
            default => $type,
        };
    }

    public static function typeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_VISIT => '&#9992;',
            self::TYPE_VIRTUAL_DATE => '&#128187;',
            self::TYPE_LIVE_DATE => '&#10084;',
            self::TYPE_ANNIVERSARY => '&#127874;',
            default => '&#9733;',
        };
    }

    public static function typeColor(string $type): string
    {
        return match ($type) {
            self::TYPE_VISIT => 'sky',
            self::TYPE_VIRTUAL_DATE => 'violet',
            self::TYPE_LIVE_DATE => 'rose',
            self::TYPE_ANNIVERSARY => 'amber',
            default => 'gray',
        };
    }
}
