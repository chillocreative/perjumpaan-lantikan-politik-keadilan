<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'email',
        'ic_number',
        '_token',
        '_ft',
        'website',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key): void {
            if (is_string($value) && ! in_array($key, $this->except, true)) {
                $value = mb_strtoupper(strip_tags($value));
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
