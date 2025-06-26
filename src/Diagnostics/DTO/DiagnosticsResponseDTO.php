<?php

namespace App\Diagnostics\DTO;


/**
 * Data Transfer Object (DTO) for outgoing diagnostics responses. This DTO provides a structured format for the data returned by the diagnostics system, making it consistent for API consumers or web dashboard rendering.
 */
class DiagnosticsResponseDTO
{
    /**
     * @param array<string, array<string, mixed>|string> $diagnostics An array of diagnostic data, typically keyed by provider name, where values can be nested arrays or formatted strings.
     * @param array<string, mixed> $metadata An associative array for general metadata about the response (e.g., timestamp, available providers).
     * @param float $executionTime The time taken to collect diagnostics, in seconds.
     */
    public function __construct(
        public readonly array $diagnostics,
        public readonly array $metadata,
        public readonly float $executionTime
    ) {}
}
