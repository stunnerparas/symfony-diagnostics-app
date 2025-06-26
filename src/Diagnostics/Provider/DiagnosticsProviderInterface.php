<?php

namespace App\Diagnostics\Provider;

/**
This interface ensures that any class implementing it will provide a unique key, gather diagnostic data, report its enabled status,and define a priority for ordering.
 */
interface DiagnosticsProviderInterface
{

    public function getKey(): string;

    public function getDiagnostics(): array;

    public function isEnabled(): bool;

    public function getPriority(): int;
}
