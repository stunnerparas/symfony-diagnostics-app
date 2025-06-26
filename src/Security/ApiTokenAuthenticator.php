<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Custom API Token Authenticator for Symfony 6+.
 * Handles token-based authentication for API requests.
 *
 * Implements:
 * - AbstractAuthenticator: The base class for custom authenticators in Symfony 6+.
 * - AuthenticationEntryPointInterface: To handle cases where authentication is required but not provided.
 */
class ApiTokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private string $apiToken;

    /**
     * @param string $apiToken The static API token (e.g., from APP_API_TOKEN environment variable).
     */
    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    /**
     * Called on every request to decide if this authenticator should be used.
     * Return `false` to skip this authenticator.
     *
     * This authenticator supports requests with an 'Authorization: Bearer' header
     * and a path starting with '/api/', excluding '/api/login_check' (which is handled by LoginController).
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ')
            && str_starts_with($request->getPathInfo(), '/api/')
            && $request->getPathInfo() !== '/api/login_check';
    }

    /**
     * Called on every request that `supports()` returns `true` for.
     * This method must return a `Passport` object.
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        if ($apiToken !== $this->apiToken) {
            throw new CustomUserMessageAuthenticationException('Invalid API token.');
        }

        return new SelfValidatingPassport(
            new UserBadge('api_user'),
            [new CustomCredentials(function ($credentials, $user) { return true; }, $apiToken)]
        );
    }

    /**
     * This method is called when authentication succeeds.
     * For stateless APIs, typically return null to allow the request to proceed.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * This method is called when authentication fails.
     * Returns a JSON response with an error message and a 401 status code.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => $exception->getMessage(),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * This method is called when an anonymous user attempts to access a resource
     * that requires authentication. It should return a `Response` object to
     * inform the user that authentication is required.
     * It's part of the AuthenticationEntryPointInterface.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = [
            'message' => 'Authentication Required.',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
