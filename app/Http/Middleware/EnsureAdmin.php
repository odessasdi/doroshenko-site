<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if (!$user->is_admin) {
            $locale = app()->getLocale();
            $supportedLocales = ['en', 'de', 'ua'];

            if (!in_array($locale, $supportedLocales, true)) {
                $locale = 'en';
            }

            return redirect()->route('pending-approval', ['locale' => $locale]);
        }

        return $next($request);
    }
}
