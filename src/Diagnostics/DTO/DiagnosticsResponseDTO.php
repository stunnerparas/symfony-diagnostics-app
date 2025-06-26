<?php

namespace App\Diagnostics\DTO;

use OpenApi\Attributes as OA;

/**
 * Data Transfer Object for diagnostics API response.
 */
#[OA\Schema(
    description: "Response DTO for system diagnostics",
    type: "object"
)]
class DiagnosticsResponseDTO
{
    /**
     * @param array<string, array<string, mixed>|array<string, string>> $diagnostics // PHPStan fix
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        #[OA\Property(type: "object", description: "Collected diagnostic data, keyed by provider name")]
        public readonly array $diagnostics, // PHPStan fix: This array holds mixed data
        #[OA\Property(type: "object", description: "Metadata about the response, e.g., timestamp, available providers")]
        public readonly array $metadata, // PHPStan fix: This array holds mixed data
        #[OA\Property(type: "number", format: "float", description: "Time taken to collect diagnostics in seconds")]
        public readonly float $executionTime
    ) {}
}
