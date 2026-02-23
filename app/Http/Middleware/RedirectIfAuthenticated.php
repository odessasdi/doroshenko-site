<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $locale = app()->getLocale();
                $supportedLocales = ['en', 'de', 'ua'];

                if (!in_array($locale, $supportedLocales, true)) {
                    $locale = 'en';
                }

                if ($user && $user->is_admin) {
                    return redirect('/admin/works');
                }

                return redirect()->route('pending-approval', ['locale' => $locale]);
            }
        }

        return $next($request);
    }
}
