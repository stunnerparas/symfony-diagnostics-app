<?php

namespace App\Diagnostics\Provider;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Provides diagnostic information about the PHP environment.
 */
#[AsTaggedItem('diagnostics.provider')]
class PhpDiagnosticsProvider extends AbstractDiagnosticsProvider
{
    public function getKey(): string
    {
        return 'php';
    }

    public function getDiagnostics(): array
    {
        return [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'display_errors' => (bool) ini_get('display_errors'),
        ];
    }
}
