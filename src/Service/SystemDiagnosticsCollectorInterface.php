<?php

namespace App\Service;

use App\Diagnostics\Exception\InvalidProviderException;
use App\Diagnostics\Provider\DiagnosticsProviderInterface;

/**
 * Interface for SystemDiagnosticsCollector.
 */
interface SystemDiagnosticsCollectorInterface
{
    public function collect(array $include = []): array;
    public function getAvailableProviders(): array;
}
