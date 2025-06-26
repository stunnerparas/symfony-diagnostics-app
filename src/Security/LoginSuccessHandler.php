<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface; // <-- ADDED THIS LINE

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private string $apiToken;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        return new JsonResponse([
            'message' => 'Authentication successful!',
            'token' => $this->apiToken,
        ], Response::HTTP_OK);
    }
}
