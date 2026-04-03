<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = ['inviter_id', 'token', 'expires_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function generateFor(User $user): self
    {
        // Delete any existing invitations from this user
        static::where('inviter_id', $user->id)->delete();

        return static::create([
            'inviter_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(48),
        ]);
    }
}
