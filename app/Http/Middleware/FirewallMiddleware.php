<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirewallMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $clientIP = $request->getClientIp();

        if (in_array($clientIP, config('firewall.ipBlacklist', []))) {
            return response('', 403);
        }

        try {
            $geoIPLookup = geoip()->getLocation($clientIP);

            if (in_array($geoIPLookup->iso_code, config('firewall.countryBlacklist', []))) {
                return response('', 403);
            }
        } catch (\Exception $ex) {
            // continue
        }

        return $next($request);
    }
}
