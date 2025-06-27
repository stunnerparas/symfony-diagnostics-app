<?php

namespace App\Service;

use App\Diagnostics\Exception\InvalidProviderException; // Keep this if InvalidProviderException is thrown by implementations

/**
 * Interface for SystemDiagnosticsCollector.
 */
interface SystemDiagnosticsCollectorInterface
{
    /**
     * Collects diagnostics from specified providers or all enabled providers if none specified.
     *
     * @param array<string> $include Optional. An array of provider keys to include.
     * If null or empty, all enabled providers are collected.
     * @return array<string, array<string, mixed>|array<string, string>> An associative array of diagnostic data,
     * keyed by provider key.
     * @throws InvalidProviderException If an included provider key is not found (if implemented by concrete collector).
     */
    public function collect(array $include = []): array;

    /**
     * Returns a list of all available diagnostic provider keys.
     * @return array<string>
     */
    public function getAvailableProviders(): array;
}
