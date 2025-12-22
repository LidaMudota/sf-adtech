<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            return redirect()->route('login')->withErrors(['auth' => 'Требуется активный пользователь.']);
        }

        if ($user->role !== $role && $user->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
