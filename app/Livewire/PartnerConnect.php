<?php

namespace App\Livewire;

use App\Mail\PartnerInvitation;
use App\Models\Couple;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class PartnerConnect extends Component
{
    public string $partnerEmail = '';
    public string $statusMessage = '';
    public string $statusType = '';

    public function mount(): void
    {
        // Auto-accept if user arrived via invitation link
        $token = session()->pull('join_token');
        if ($token) {
            $this->acceptToken($token);
        }
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'partnerEmail' => 'required|email',
        ]);

        $user = auth()->user();

        if ($user->couple_id) {
            $this->setStatus('Du bist bereits verbunden!', 'warning');
            return;
        }

        if ($this->partnerEmail === $user->email) {
            $this->setStatus('Du kannst dich nicht selbst einladen!', 'error');
            return;
        }

        $invitation = Invitation::generateFor($user);

        Mail::to($this->partnerEmail)->send(new PartnerInvitation($user, $invitation));

        $this->partnerEmail = '';
        $this->setStatus('Einladung wurde an die E-Mail gesendet!', 'success');
    }

    public function render()
    {
        $user = auth()->user()->fresh();
        $partner = $user->couple_id ? $user->partner() : null;

        return view('livewire.partner-connect', [
            'isConnected' => (bool) $user->couple_id,
            'partner' => $partner,
        ]);
    }

    private function acceptToken(string $token): void
    {
        $user = auth()->user();

        if ($user->couple_id) return;

        $invitation = Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation || $invitation->inviter_id === $user->id) {
            $this->setStatus('Einladung ungültig oder abgelaufen.', 'error');
            return;
        }

        DB::transaction(function () use ($user, $invitation) {
            $couple = Couple::create([
                'user1_id' => $invitation->inviter_id,
                'user2_id' => $user->id,
                'paired_at' => now(),
            ]);

            $invitation->inviter->update(['couple_id' => $couple->id]);
            $user->update(['couple_id' => $couple->id]);

            Event::where('created_by', $user->id)->whereNull('couple_id')->update(['couple_id' => $couple->id]);
            Event::where('created_by', $invitation->inviter_id)->whereNull('couple_id')->update(['couple_id' => $couple->id]);

            $invitation->delete();
        });

        $this->setStatus('Erfolgreich verbunden!', 'success');
    }

    private function setStatus(string $message, string $type): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }
}
