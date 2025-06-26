<?php

namespace App\Diagnostics\Provider;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Provides application-specific diagnostic information.
 * Temporarily modified to include APP_API_TOKEN for debugging authentication issues.
 */
#[AsTaggedItem('diagnostics.provider')] // Tags for automatic discovery
class ApplicationDiagnosticsProvider extends AbstractDiagnosticsProvider
{
    private string $appVersion;
    private ?string $apiToken;

    /**
     * @param string $appVersion The version of the application.
     * @param string|null $apiToken The API token from environment, injected for debugging.
     */
    public function __construct(
        string $appVersion = '1.0.0-DEV',
        // Inject the APP_API_TOKEN directly from the environment for debugging purposes.
        // This parameter will be automatically wired by Symfony from '%env(APP_API_TOKEN)%'.
        string $apiToken = null // Default to null for safety if env var isn't set
    ) {
        $this->appVersion = $appVersion;
        $this->apiToken = $apiToken;
    }

    /**
     * Returns the unique key for this provider.
     */
    public function getKey(): string
    {
        return 'application';
    }

    /**
     * Collects application diagnostic data, now including the API token.
     */
    public function getDiagnostics(): array
    {
        return [
            'version' => $this->appVersion,
            'api_token_as_seen_by_app' => $this->apiToken,
        ];
    }
}
