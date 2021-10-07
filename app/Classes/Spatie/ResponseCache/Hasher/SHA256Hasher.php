<?php

namespace App\Classes\Spatie\ResponseCache\Hasher;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\RequestHasher;

class SHA256Hasher implements RequestHasher
{
    public function getHashFor(Request $request): string
    {
        return 'volistx-caching:' . hash('sha256', "{$request->getHost()}-{$request->getRequestUri()}-{$request->getMethod()}/" . $this->cacheProfile->useCacheNameSuffix($request));
    }
}
