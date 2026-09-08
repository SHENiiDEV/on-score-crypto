<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next, ?string $requiredScope = null): Response
    {
        $keyId = $request->header('X-API-Key') ?? $request->header('X-Key-Id');
        $secret = $request->header('X-API-Secret') ?? $request->header('X-Key-Secret');

        // Support Bearer token format: "Bearer key_id:secret"
        if (!$keyId && $authHeader = $request->header('Authorization')) {
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                if (str_contains($token, ':')) {
                    [$keyId, $secret] = explode(':', $token, 2);
                } else {
                    $keyId = $token;
                }
            }
        }

        if (!$keyId) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Missing API Key in headers (X-API-Key / X-API-Secret).',
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 401);
        }

        /** @var ApiClient $apiClient */
        $apiClient = ApiClient::with('account')->where('key_id', $keyId)->first();

        if (!$apiClient || $apiClient->status !== 'active') {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_API_KEY',
                    'message' => 'Invalid or inactive API Key.',
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 401);
        }

        // Verify Secret if provided in headers
        if ($secret && !$apiClient->verifySecret($secret)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_API_SECRET',
                    'message' => 'Invalid API Secret.',
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 401);
        }

        // Verify Account status
        if ($apiClient->account->status !== 'active') {
            return response()->json([
                'error' => [
                    'code' => 'ACCOUNT_SUSPENDED',
                    'message' => 'Your B2B account is suspended or inactive.',
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 403);
        }

        // Verify Scopes
        if ($requiredScope && is_array($apiClient->scopes) && !in_array($requiredScope, $apiClient->scopes)) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN_SCOPE',
                    'message' => "This API Key lacks the required scope: {$requiredScope}",
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 403);
        }

        $apiClient->update(['last_used_at' => now()]);

        $request->attributes->set('account', $apiClient->account);
        $request->attributes->set('api_client', $apiClient);

        return $next($request);
    }
}
