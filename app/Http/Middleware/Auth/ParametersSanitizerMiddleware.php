<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;

class ParametersSanitizerMiddleware
{
    public bool $trim;
    public bool $emptyToNull;
    public bool $lowerCase;

    public function handle(Request $request, Closure $next, $emptyToNull, $trim, $lowerCase)
    {
        $this->lowerCase = filter_var($lowerCase, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->emptyToNull = filter_var($emptyToNull, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->trim = filter_var($trim, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $inputs = $request->all();
        $this->Sanitize($inputs);
        $request->replace($inputs);

        return $next($request);
    }

    private function Sanitize(&$inputs)
    {
        array_walk_recursive($inputs, function (&$value) {
            if ($value === '') {
                if ($this->emptyToNull) {
                    $value = null;
                }
            } else {
                if ($this->trim)
                    $value = trim($value);
                if ($this->lowerCase)
                    $value = strtolower($value);
            }
        });
    }
}