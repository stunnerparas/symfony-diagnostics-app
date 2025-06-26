<?php

namespace App\Diagnostics\Provider;

/**
 * This interface ensures that any class implementing it will provide a unique key, gather diagnostic data,
 * report its enabled status, and define a priority for ordering.
 */
interface DiagnosticsProviderInterface
{
    /**
     * Returns the unique key for this diagnostics provider (e.g., 'php', 'symfony').
     * @return string
     */
    public function getKey(): string;

    /**
     * Retrieves diagnostic information as an associative array.
     *
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array;

    /**
     * Checks if the provider is enabled.
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Sets the enabled status of the provider. (Added for consistency with AbstractDiagnosticsProvider)
     * @param bool $enabled
     * @return void
     */
    public function setEnabled(bool $enabled): void;

    /**
     * Returns the priority of this provider, used for ordering.
     * Providers with higher priority values are typically processed earlier.
     * @return int
     */
    public function getPriority(): int;
}
