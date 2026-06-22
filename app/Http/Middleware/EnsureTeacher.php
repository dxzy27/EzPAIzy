<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureTeacher
{
    /**
     * Handle an incoming request.
     * Strictly verify the user is a teacher.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Check if user's role is explicitly 'teacher'
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher') {
            Log::warning('Unauthorized teacher access attempt', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_role' => $user?->role,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return redirect('/home')->with('error', 'You do not have permission to access teacher resources. Only teachers can access this area.');
        }

        return $next($request);
    }
}
