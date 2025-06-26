<?php

namespace App\Controller;

use App\Diagnostics\DTO\DiagnosticsRequestDTO;
use App\Diagnostics\DTO\DiagnosticsResponseDTO;
use App\Service\SystemDiagnosticsCollectorInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for retrieving system and application diagnostics via API.
 */
#[Route('/api/system')]
class SystemDiagnosticsController extends AbstractController
{
    public function __construct(
        private SystemDiagnosticsCollectorInterface $collector,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    /**
     * @OA\Post(
     * path="/api/system/diagnostics",
     * summary="Get system diagnostics",
     * security={{"bearerAuth": {}}},
     * requestBody=@OA\RequestBody(
     * content=@OA\JsonContent(
     * properties={
     * @OA\Property(
     * property="include",
     * type="array",
     * items=@OA\Items(type="string"),
     * description="List of specific diagnostic keys to include (e.g., 'php', 'symfony')",
     * example={"php", "symfony"}
     * ),
     * @OA\Property(
     * property="level",
     * type="string",
     * enum={"basic", "full"},
     * description="Predefined diagnostic level ('basic' or 'full')",
     * example="basic"
     * )
     * }
     * )
     * ),
     * responses={
     * @OA\Response(
     * response=200,
     * description="Diagnostics data",
     * @OA\JsonContent(
     * properties={
     * @OA\Property(property="diagnostics", type="object", description="Collected diagnostic data"),
     * @OA\Property(property="metadata", type="object", description="Additional metadata about the response"),
     * @OA\Property(property="executionTime", type="number", format="float", description="Time taken to collect diagnostics in seconds")
     * }
     * )
     * ),
     * @OA\Response(response=400, description="Invalid request payload or unknown provider"),
     * @OA\Response(response=401, description="Unauthorized - Missing or invalid token"),
     * @OA\Response(response=403, description="Forbidden - Insufficient permissions"),
     * @OA\Response(response=500, description="Internal server error")
     * }
     * )
     */
    #[Route('/diagnostics', name: 'system_diagnostics', methods: ['POST'])]
    #[IsGranted('ROLE_API_USER')]
    public function diagnostics(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $requestDto = $this->deserializeAndValidateRequest($request);
            /** @var array<string> $include */
            $include = $this->resolveIncludeArray($requestDto);
            $diagnostics = $this->collector->collect($include);

            $response = new DiagnosticsResponseDTO(
                diagnostics: $diagnostics, // Pass original diagnostics for API
                metadata: [
                    'timestamp' => time(),
                    'available_providers' => $this->collector->getAvailableProviders()
                ],
                executionTime: microtime(true) - $startTime
            );

            return $this->json($response);

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Invalid request',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) { // Catch any other unexpected errors during execution.
            return $this->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function deserializeAndValidateRequest(Request $request): DiagnosticsRequestDTO
    {
        $requestDto = $this->serializer->deserialize(
            $request->getContent(),
            DiagnosticsRequestDTO::class,
            'json'
        );

        $violations = $this->validator->validate($requestDto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        return $requestDto;
    }

    /**
     * @param DiagnosticsRequestDTO $requestDto
     * @return array<string>
     */
    private function resolveIncludeArray(DiagnosticsRequestDTO $requestDto): array
    {
        if ($requestDto->level) {
            return match ($requestDto->level) {
                'basic' => ['php', 'symfony'],
                'full' => $this->collector->getAvailableProviders(),
                default => [],
            };
        }
        return $requestDto->include;
    }
}
