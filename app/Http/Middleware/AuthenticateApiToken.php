<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use App\Models\Setting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request and enforce Token-Based Authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for Bearer token or X-API-Key header
        $bearerToken = $request->bearerToken();
        $apiKeyHeader = $request->header('X-API-Key') ?: $request->header('X-Api-Key');
        $rawToken = $bearerToken ?: $apiKeyHeader ?: $request->query('api_token');

        if ($rawToken) {
            // A. Check Personal Access Token
            $tokenInstance = PersonalAccessToken::findToken($rawToken);
            if ($tokenInstance && $tokenInstance->tokenable) {
                $user = $tokenInstance->tokenable;
                $user->withAccessToken($tokenInstance);
                Auth::setUser($user);
                $request->setUserResolver(fn () => $user);

                return $next($request);
            }

            // B. Check Master API Secret Key (e.g. from .env or settings for server-to-server n8n bots)
            $masterKey = config('app.api_secret') ?: Setting::get('api_secret_key');
            if ($masterKey && hash_equals((string) $masterKey, (string) $rawToken)) {
                $firstUser = User::first();
                if ($firstUser) {
                    Auth::setUser($firstUser);
                    $request->setUserResolver(fn () => $firstUser);
                }

                return $next($request);
            }

            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired API token.',
                'status' => 401,
            ], 401);
        }

        // 2. Allow logged-in session user
        if (Auth::check()) {
            return $next($request);
        }

        // 3. Reject unauthenticated request with clean 401 JSON
        return response()->json([
            'success' => false,
            'error' => 'Unauthenticated. Provide a valid Bearer Token (Authorization: Bearer <token>) or X-API-Key.',
            'status' => 401,
        ], 401);
    }
}
