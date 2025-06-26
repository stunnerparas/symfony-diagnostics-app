<?php

namespace App\Service;

use App\Diagnostics\Exception\DiagnosticsException;
use App\Diagnostics\Exception\InvalidProviderException;
use App\Diagnostics\Provider\DiagnosticsProviderInterface;
use Psr\Log\LoggerInterface; // Recommended: For proper error logging

/**
 * Gathers and aggregates diagnostic data from various providers.
 * This class acts as the central coordinator for the diagnostics system.
 * It manages which providers to collect from, handles potential errors during collection, and can filter providers based on request criteria.
 */
class SystemDiagnosticsCollector implements SystemDiagnosticsCollectorInterface // <--- ADD THIS INTERFACE IMPLEMENTATION
{
    /**
     * @var DiagnosticsProviderInterface[] An array of registered diagnostic providers,
     * indexed by their unique keys for efficient lookup.
     */
    private array $providers = [];

    // Recommended: Inject a LoggerInterface for proper error logging.
    // public function __construct(iterable $providers, private LoggerInterface $logger)
    // {
    //     foreach ($providers as $provider) {
    //         $this->providers[$provider->getKey()] = $provider;
    //     }
    // }

    /**
     * @param iterable<DiagnosticsProviderInterface> $providers An iterable collection
     * of all registered diagnostic providers.
     * Symfony's service container injects these via tagging (`!tagged_iterator`).
     */
    public function __construct(iterable $providers)
    {
        // Store providers in an associative array for easy lookup by their unique key.
        foreach ($providers as $provider) {
            $this->providers[$provider->getKey()] = $provider;
        }
    }

    /**
     * Collects diagnostic data based on the provided include list.
     *
     * If the `$include` array is empty, all enabled providers will be collected.
     * Otherwise, only the specified providers will be collected.
     *
     * @param array $include An optional array of provider keys to include (e.g., ['php', 'symfony']).
     * @return array An associative array of diagnostic results, where keys are provider keys.
     * If a provider fails, its value will contain an error message.
     * @throws InvalidProviderException If an unknown provider key is requested in the $include list.
     * @inheritDoc
     */
    public function collect(array $include = []): array
    {
        // Determine which providers to collect from: all available or a filtered subset.
        $providersToCollect = empty($include) ? $this->providers : $this->filterProviders($include);

        $result = [];
        // Iterate through the selected providers and collect their diagnostics.
        foreach ($providersToCollect as $key => $provider) {
            // Only collect from providers that are currently enabled.
            if (!$provider->isEnabled()) {
                continue; // Skip disabled providers.
            }

            try {
                // Attempt to get diagnostics from the provider.
                $result[$key] = $provider->getDiagnostics();
            } catch (\Throwable $e) {

                $result[$key] = [
                    'error' => (new DiagnosticsException($key, $e->getMessage(), $e))->getMessage()
                ];
            }
        }

        return $result;
    }


    private function filterProviders(array $include): array
    {
        $filtered = [];
        foreach ($include as $key) {
            // Check if the requested provider key actually exists.
            if (!isset($this->providers[$key])) {
                // If not, throw an exception to indicate an invalid request.
                throw new InvalidProviderException($key);
            }
            $filtered[$key] = $this->providers[$key];
        }
        return $filtered;
    }


    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
