<?php

namespace App\Diagnostics\Provider;

/**
 * This class provides default implementations for common properties and methods defined in DiagnosticsProviderInterface, such as 'enabled' status and 'priority'.
 */
abstract class AbstractDiagnosticsProvider implements DiagnosticsProviderInterface
{
    protected bool $enabled = true;

    protected int $priority = 0;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    abstract public function getKey(): string;
    /**
     * @return array<string, mixed>
     */
    abstract public function getDiagnostics(): array;
}
