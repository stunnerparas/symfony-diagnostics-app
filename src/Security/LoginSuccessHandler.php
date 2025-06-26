<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Handles successful API login authentication.
 *
 * This service is configured as the 'success_handler' for the 'json_login' firewall.
 * Upon successful authentication, it generates a JSON response containing a success
 * message and the API token.
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @param ApiTokenAuthenticator $apiTokenAuthenticator The authenticator to generate the token.
     */
    public function __construct(
        private ApiTokenAuthenticator $apiTokenAuthenticator
    ) {
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * method is not called when an authentication token is created manually.
     *
     * @param Request        $request The request that triggered the authentication.
     * @param TokenInterface $token   The full authentication token.
     *
     * @return Response Never null, must return a Response object.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $apiToken = $this->apiTokenAuthenticator->createAuthenticatedToken($user, 'main');

        return new JsonResponse([
            'message' => 'Authentication successful!',
            'token' => $apiToken->getCredentials(),
        ], Response::HTTP_OK);
    }
}
