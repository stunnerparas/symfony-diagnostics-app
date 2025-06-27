<?php

namespace App\Diagnostics\Provider;

use App\Service\DiagnosticsProviderInterface;
// Removed: use Symfony\Component\HttpFoundation\RequestStack; // This was removed to address 'never read, only written' warning
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Provides diagnostic information about the Symfony environment.
 *
 * This provider collects details about the Symfony application, such as its
 * version, environment, debug mode, and directory writability.
 * It's automatically tagged as a 'diagnostics.provider' service in Symfony.
 */
#[AsTaggedItem('diagnostics.provider')]
class SymfonyDiagnosticsProvider extends AbstractDiagnosticsProvider implements DiagnosticsProviderInterface // FIX: Now extends AbstractDiagnosticsProvider
{
    private string $projectDir;

    /**
     * @param string $projectDir The root directory of the Symfony project.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Returns the unique key for this diagnostic provider.
     *
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'symfony';
    }

    /**
     * Gathers and returns diagnostic data specific to the Symfony application.
     *
     * @inheritDoc
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array
    {
        $env = $_ENV['APP_ENV'] ?? 'dev';

        $symfonyVersion = Kernel::VERSION;

        $debugMode = ($env === 'dev');

        $cacheDir = $this->projectDir . '/var/cache/' . $env;
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $cacheWritable = is_dir($cacheDir) && is_writable($cacheDir);


        $logDir = $this->projectDir . '/var/log';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logWritable = is_dir($logDir) && is_writable($logDir);

        return [
            'symfony_version' => $symfonyVersion,
            'environment' => $env,
            'debug_mode' => $debugMode,
            'cache_directory_writable' => $cacheWritable,
            'logs_directory_writable' => $logWritable,
            'php_cli_path' => PHP_BINARY,
            'php_ini_path' => php_ini_loaded_file(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }

    /**
     * Checks if this diagnostic provider is currently enabled.
     * This method is now inherited from AbstractDiagnosticsProvider.
     * @inheritDoc
     */

    /**
     * Returns the priority of this provider, used for ordering.
     * This method is now inherited from AbstractDiagnosticsProvider.
     * @inheritDoc
     */

    /**
     * Returns a human-readable name for the provider.
     * @return string
     */
    public function getName(): string
    {
        return 'Symfony Application';
    }

}
