<?php

namespace App\Service;

/**
 * Defines the contract for all system diagnostic providers.
 * Each provider is responsible for gathering a specific set of diagnostic data.
 *
 * This interface ensures that any class implementing it will provide
 * a unique key, gather diagnostic data, report its enabled status,
 * and define a priority for ordering.
 */
interface DiagnosticsProviderInterface
{
    /**
     * Returns the unique key for this diagnostic provider (e.g., 'php', 'symfony', 'database').
     * This key is used to identify the provider and filter diagnostics.
     *
     * @return string The unique identifier for the diagnostic provider.
     */
    public function getKey(): string; // Changed from getName() to getKey()

    /**
     * Gathers and returns the diagnostic data for this provider.
     * The structure of the returned array is specific to each provider.
     *
     * @return array An associative array of diagnostic data.
     */
    public function getDiagnostics(): array;

    /**
     * Checks if this diagnostic provider is currently enabled.
     * Disabled providers will be skipped during the diagnostics collection process.
     *
     * @return bool True if the provider is enabled, false otherwise.
     */
    public function isEnabled(): bool;

    /**
     * Returns the priority of this provider, used for ordering.
     * Providers with a higher integer value will be processed first.
     *
     * @return int The priority level (e.g., 100 for high priority, 0 for default).
     */
    public function getPriority(): int;
}
