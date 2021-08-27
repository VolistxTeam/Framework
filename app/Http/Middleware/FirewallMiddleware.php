<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Wikimedia\IPSet;

class FirewallMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $clientIP = $request->getClientIp();

        $ipSet = new IPSet(config('firewall.ipBlacklist', []));

        if ($ipSet->match($clientIP)) {
            return response('', 403);
        }

        try {
            $geoIPLookup = geoip()->getLocation($clientIP);

            if (in_array($geoIPLookup->iso_code, config('firewall.countryBlacklist', []))) {
                return response('', 403);
            }
        } catch (Exception $ex) {
            // continue
        }

        $response = $next($request);
        $response->header('Server', 'WebShield/2.84-stable');

        return $response;
    }
}
