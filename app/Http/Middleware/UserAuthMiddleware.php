<?php

namespace App\Http\Middleware;

use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use Closure;
use RandomLib\Factory;

class UserAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $personalKeys = PersonalKeys::where('key', $request->input('access_key', ''))->first();

        if (empty($personalKeys)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'No token was specified or an invalid token was specified.'
                ]
            ], 403);
        }

        $logs = Logs::where('key_id', $personalKeys->id)->whereMonth('created_at', Carbon::now()->month)->get()->toArray();

        if (count($logs) >= $personalKeys->max_count) {
            return response()->json([
                'error' => [
                    'type' => 'xUsageLimitReached',
                    'info' => 'The maximum allowed amount of monthly API requests has been reached. Please upgrade your plan.'
                ]
            ], 429);
        }

        $factory = new Factory;
        $generator = $factory->getMediumStrengthGenerator();

        $randomRayID = strtolower($generator->generateString(16));

        $log = [
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ];

        $newLog = new Logs();
        $newLog->key_id = $personalKeys->id;
        $newLog->request_id = $randomRayID;
        $newLog->request_info = $log;
        $newLog->access_ip = $request->getClientIp();
        $newLog->save();

        $response = $next($request);
        $response->header('X-Request-ID', $randomRayID);

        return $response;
    }
}
