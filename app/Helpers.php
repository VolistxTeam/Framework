<?php

use App\Models\PersonalKeys;
use Carbon\Carbon;

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string $path
     * @return string
     */
    function public_path($path = '')
    {
        return env('PUBLIC_PATH', base_path('public')) . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @param  string  $name
     * @return mixed
     */
    function resolve($name)
    {
        return app($name);
    }
}

function now($timezone = null)
{
    return Carbon::now($timezone);
}

function checkUserKeyPermission($token, $name): bool
{
    $accessKey = PersonalKeys::query()->where('key', $token)->first();

    if (empty($accessKey)) {
        return false;
    }

    if (in_array('*', $accessKey->permissions)) {
        return true;
    }

    if (in_array($name, $accessKey->permissions)) {
        return true;
    }

    return false;
}