<?php

namespace App\Service;

use App\Diagnostics\Exception\InvalidProviderException;
use Psr\Log\LoggerInterface;
use App\Service\DiagnosticsProviderInterface;

/**
 * Service for collecting system diagnostics from various providers.
 */
class SystemDiagnosticsCollector implements SystemDiagnosticsCollectorInterface
{
    /**
     * @var array<string, DiagnosticsProviderInterface>
     */
    private array $providers = [];
    private LoggerInterface $logger;

    /**
     * @param iterable<DiagnosticsProviderInterface> $providers
     * @param LoggerInterface $logger
     */
    public function __construct(iterable $providers, LoggerInterface $logger)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getKey()] = $provider;
        }
        $this->logger = $logger;
    }

    /**
     * @return array<string>
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Collects diagnostics from specified providers or all enabled providers if none specified.
     *
     * @param array<string>|null $include Optional. An array of provider keys to include.
     * If null or empty, all enabled providers are collected.
     * @return array<string, array<string, mixed>|array<string, string>> An associative array of diagnostic data,
     * keyed by provider key. Each value is the
     * diagnostic data from that provider, or an
     * error array if the provider failed.
     * @throws InvalidProviderException If an included provider key is not found.
     */
    public function collect(?array $include = null): array
    {
        $diagnostics = [];
        $providersToCollect = $include ? $this->filterProviders($include) : $this->getEnabledProviders();

        foreach ($providersToCollect as $key => $provider) {
            try {
                $diagnostics[$key] = $provider->getDiagnostics();
            } catch (\Throwable $e) {
                $errorMessage = sprintf('Diagnostics error in provider "%s": %s', $key, $e->getMessage());
                $this->logger->error($errorMessage, ['exception' => $e, 'provider' => $key]);
                $diagnostics[$key] = ['error' => $errorMessage];
            }
        }

        return $diagnostics;
    }

    /**
     * Filters the registered providers based on the given include list.
     *
     * @param array<string> $include An array of provider keys to include.
     * @return array<string, DiagnosticsProviderInterface> Filtered list of providers.
     * @throws InvalidProviderException If any key in $include is not a valid provider.
     */
    private function filterProviders(array $include): array
    {
        $filtered = [];
        foreach ($include as $key) {
            if (!isset($this->providers[$key])) {
                throw new InvalidProviderException(sprintf('Invalid diagnostics provider: "%s"', $key));
            }
            $filtered[$key] = $this->providers[$key];
        }
        return $filtered;
    }

    /**
     * Gets all currently enabled providers.
     *
     * @return array<string, DiagnosticsProviderInterface>
     */
    private function getEnabledProviders(): array
    {
        return array_filter($this->providers, fn (DiagnosticsProviderInterface $p) => $p->isEnabled());
    }
}
