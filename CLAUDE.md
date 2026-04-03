# CLAUDE.md — SSJTimer

## Project Overview

SSJTimer (BridgeTheGap) is a long-distance relationship synchronization web app. It features a live countdown timer, progress bar, shared calendar for flights/visits, and real-time partner sync via WebSockets.

## Tech Stack (TALL Stack)

- **Laravel 13.x** (PHP 8.3+) — Backend framework
- **Livewire 4.x** — Server-driven UI with Islands architecture
- **Alpine.js 3.x/4.x** — Client-side timer logic and DOM manipulation
- **Tailwind CSS v4** — Styling with JIT compilation
- **Laravel Reverb 1.x** — Native WebSocket server for real-time sync
- **Laravel Breeze** — Authentication (Livewire Edition)

## Commands

```bash
# Setup
composer install
npm install
php artisan migrate

# Development
php artisan serve        # Backend
npm run dev              # Frontend (Vite)
php artisan reverb:start # WebSocket server

# Testing
php artisan test

# Production build
npm run build
php artisan config:cache
php artisan route:cache
```

## Architecture Decisions

### UTC Everything
All dates are stored in UTC. Display conversion happens via Carbon macro `inUserTimezone()`. Never store local times in the database.

### Timer runs client-side
The countdown timer runs entirely in Alpine.js using `setInterval`. The backend only provides initial UTC timestamps as Unix milliseconds. This avoids constant server round-trips.

### Livewire Islands
Only changed components re-render. Calendar updates don't refresh timer or navigation. Use `@island` directives.

### Multi-Tenancy via couple_id
Every event query must scope by `couple_id`. Laravel Global Scopes enforce this at the model level — never query events without it.

### State Machine
- **Anticipation**: Countdown to arrival (blue/violet palette)
- **Together**: During visit, shows stay duration progress (green palette)
- `tracking_start` field = 0% baseline for progress bar

### Progress Bar Formula
```
percentage = clamp((Now - Start) / (Target - Start) * 100, 0, 100)
```
Width via inline styles, colors via Tailwind classes, smooth transitions via `transition-all duration-1000`.

## Database Schema (4 core tables)

- **users** — id, name, email, password, timezone, avatar_path, couple_id (FK, nullable)
- **couples** — id, user1_id, user2_id, paired_at
- **invitations** — id, inviter_id, token (64-char), expires_at (48h)
- **events** — id, couple_id, created_by, type (flight/visit/date), title, start_time (UTC), end_time (nullable), tracking_start

## Pairing System

Token-based invitation with 48-hour expiry. Pairing is atomic (DB transaction). Token is hard-deleted after use. Rate-limit invitation endpoints.

## Security

- Global scopes enforce couple_id isolation
- XSS prevention via `{{ }}` escaping
- CSRF tokens on all forms
- WebSocket auth via private channels (keyed by couple_id)
- Rate limiting on invitation endpoints

## Development Phases

1. Laravel setup, migrations, Breeze auth, profile management
2. Token generation, invitation UI, pairing middleware
3. Event management components, timezone normalization
4. Alpine.js timer, progress bar, state transitions
5. Laravel Reverb, broadcasting, testing, deployment

## Reference

Full architectural spec: [Docs/BridgeTheGap.pdf](Docs/BridgeTheGap.pdf)
