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

    const RECURRENCE_RULES = ['weekly', 'biweekly', 'monthly'];

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
     * These are not persisted — they're created on-the-fly for display.
     */
    public function generateOccurrences(Carbon $from, Carbon $to): array
    {
        if (!$this->isRecurring()) {
            return [];
        }

        $occurrences = [];
        $current = $this->start_time->copy();
        $until = $this->recurrence_until
            ? Carbon::parse($this->recurrence_until)->endOfDay()
            : $to;

        // Don't generate past the requested range
        $until = $until->lt($to) ? $until : $to;

        while ($current->lte($until)) {
            if ($current->gte($from) && !$current->eq($this->start_time)) {
                $duration = $this->end_time
                    ? $this->start_time->diffInMinutes($this->end_time)
                    : 180; // default 3h

                $occurrence = new self($this->toArray());
                $occurrence->id = $this->id;
                $occurrence->start_time = $current->copy();
                $occurrence->end_time = $current->copy()->addMinutes($duration);
                $occurrence->setRelation('creator', $this->creator);
                $occurrences[] = $occurrence;
            }

            $current = match ($this->recurrence_rule) {
                'weekly' => $current->addWeek(),
                'biweekly' => $current->addWeeks(2),
                'monthly' => $current->addMonth(),
                default => $current->addWeek(),
            };
        }

        return $occurrences;
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
