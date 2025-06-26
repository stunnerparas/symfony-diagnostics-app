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
     * @param array<string, array<string, mixed>|array<string, string>> $diagnostics An array of diagnostic data, typically keyed by provider name, where values can be nested arrays or formatted strings.
     * @param array<string, mixed> $metadata An associative array for general metadata about the response (e.g., timestamp, available providers).
     * @param float $executionTime The time taken to collect diagnostics, in seconds.
     */
    public function __construct(
        public readonly array $diagnostics,
        public readonly array $metadata,
        public readonly float $executionTime
    ) {}
}
