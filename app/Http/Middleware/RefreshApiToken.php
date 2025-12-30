<?php

namespace App\Http\Middleware;

use App\Services\ApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshApiToken
{
    protected $tokenService;

    public function __construct(ApiTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Handle an incoming request.
     * Check if token is expired and refresh if needed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if token is expired and refresh if needed
        if ($this->tokenService->isTokenExpired()) {
            $this->tokenService->fetchToken();
        }

        return $next($request);
    }
}

