<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScavengerHuntAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('gate') || $request->is('gate/*')) {
            return $next($request);
        }

        if (!$request->session()->get('scavenger_hunt_passed')) {
            return redirect('/gate');
        }

        return $next($request);
    }
}
