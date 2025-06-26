<?php

namespace App\Diagnostics\DTO;

/**
 * Data Transfer Object (DTO) for outgoing diagnostics responses. his DTO provides a structured format for the data returned by the diagnostics system, making it consistent for API consumers or web dashboard rendering.
 */
class DiagnosticsResponseDTO
{
    public function __construct(
        public array $diagnostics = [],
        public array $metadata = [],
        public float $executionTime = 0.0
    ) {}
}
