<?php

namespace App\Diagnostics\Provider;

use App\Service\DiagnosticsProviderInterface; // Retain for clarity, though it implies the interface
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Provides diagnostic information about the Symfony environment.
 *
 * This provider collects details about the Symfony application, such as its
 * version, environment, debug mode, and directory writability.
 * It's automatically tagged as a 'diagnostics.provider' service in Symfony.
 */
#[AsTaggedItem('diagnostics.provider')]
class SymfonyDiagnosticsProvider implements DiagnosticsProviderInterface
{
    private string $projectDir;
    private ?RequestStack $requestStack;

    /**
     * @param string $projectDir The root directory of the Symfony project.
     * @param RequestStack|null $requestStack Symfony's request stack service (optional).
     */
    public function __construct(string $projectDir, RequestStack $requestStack = null)
    {
        $this->projectDir = $projectDir;
        $this->requestStack = $requestStack;
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
     */
    public function getDiagnostics(): array
    {
        $env = $_ENV['APP_ENV'] ?? 'dev';

        $symfonyVersion = \Symfony\Component\HttpKernel\Kernel::VERSION;

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
     *
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Returns the priority of this provider, used for ordering.
     *
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 50;
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }
}
