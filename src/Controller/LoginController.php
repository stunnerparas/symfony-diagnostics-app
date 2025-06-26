<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA; // Ensure this is imported for OpenAPI annotations

/**
 * Controller for handling user login and token generation.
 * This controller will now manually verify username/password for token issuance.
 */
class LoginController extends AbstractController
{
    /**
     * @OA\Post(
     * path="/api/login_check",
     * summary="Authenticate and get an API token",
     * requestBody=@OA\RequestBody(
     * required=true,
     * content=@OA\JsonContent(
     * type="object",
     * @OA\Property(property="username", type="string", example="api_user"),
     * @OA\Property(property="password", type="string", example="your_secret_token")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Authentication successful",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="Authentication successful!"),
     * @OA\Property(property="token", type="string", example="your_secret_token_from_env")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid credentials"
     * )
     * )
     */
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $expectedUsername = 'api_user';
        $expectedPassword = $_ENV['APP_API_TOKEN'] ?? null;

        if (
            empty($data['username']) || $data['username'] !== $expectedUsername ||
            empty($data['password']) || $data['password'] !== $expectedPassword
        ) {
            return $this->json([
                'message' => 'Invalid credentials.'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'message' => 'Authentication successful!',
            'token' => $expectedPassword
        ]);
    }
}
