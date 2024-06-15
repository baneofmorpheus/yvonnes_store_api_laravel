<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Auth\AuthService;
use  App\Helpers\ApiResponse;
use Exception;

class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth_header = $request->header('Authorization');

        if (!$auth_header) {
            return ApiResponse::errorResponse('Authorization header missing', 401);
        }

        list($type, $token_type, $token) = explode(' ', $auth_header, 3);

        if ($type !== 'Bearer' || !$token_type || !$token) {
            return ApiResponse::errorResponse('Invalid authorization header format', 401);
        }

        try {
            $verified_user = null;
            switch ($token_type) {
                case 'Google':
                    $verified_user = AuthService::getUserWithGoogleToken(['id_token' => $token]);

                    break;

                default:
                    return ApiResponse::errorResponse('Unsupported token type', 401);
            }
            if (!isset($verified_user)) {
                return ApiResponse::errorResponse('Invalid token', 401);
            }
        } catch (Exception $e) {
            return ApiResponse::errorResponse($e->getMessage(), 401);
        }

        return $next($request);
    }
}
