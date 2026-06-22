<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureStudent
{
    /**
     * Handle an incoming request.
     * Strictly verify the user is a student.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Check if user's role is explicitly 'student'
        $user = auth()->user();
        if (!$user || $user->role !== 'student') {
            Log::warning('Unauthorized student access attempt', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_role' => $user?->role,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return redirect('/home')->with('error', 'You do not have permission to access student resources.');
        }

        return $next($request);
    }
}
