<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Wikimedia\IPSet;
use function config;
use function geoip;
use function response;

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
        $response->header('X-Protected-By', 'WebShield/3.16');

        return $response;
    }
}
