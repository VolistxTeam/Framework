<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Closure;
use Illuminate\Http\Request;

class Locale
{
    public function handle(Request $request, Closure $next): Response
    {
        $acceptableLocales = ['en', 'ko', 'ru', 'jp', 'zh'];
        $userLocales = $request->getLanguages();
        $selectedLocale = false;

        if (! empty($userLocales)) {
            foreach ($userLocales as $lang) {
                $langToSearch = str_replace('_', '-', $lang);
                if (in_array($langToSearch, $acceptableLocales)) {
                    app('translator')->setLocale($langToSearch);
                    $selectedLocale = true;
                    break;
                }
            }
        }

        if (! $selectedLocale) {
            app('translator')->setLocale('en'); // fallback
        }

        return $next($request);
    }
}
