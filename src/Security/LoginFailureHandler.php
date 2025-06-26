<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Handles failed API login authentication.
 *
 * This service is configured as the 'failure_handler' for the 'json_login' firewall.
 * Upon authentication failure, it generates a JSON error response with an appropriate
 * message and a 401 Unauthorized status code.
 */
class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * This is called when an authentication attempt fails.
     *
     * @param Request                 $request   The request that triggered the authentication failure.
     * @param AuthenticationException $exception The exception that was thrown during authentication.
     *
     * @return Response Never null, must return a Response object.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'message' => 'Authentication failed: ' . $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
