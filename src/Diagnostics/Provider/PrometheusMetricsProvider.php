<?php

namespace App\Diagnostics\Provider;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Provides Prometheus-style metrics as diagnostics.
 */
#[AsTaggedItem('diagnostics.provider')]
class PrometheusMetricsProvider extends AbstractDiagnosticsProvider
{
    public function getKey(): string
    {
        return 'prometheus';
    }

    public function getDiagnostics(): array
    {
        return [
            'http_requests_total' => $this->getHttpRequestsTotal(),
            'memory_usage_bytes' => memory_get_usage(true),
            'response_time_seconds' => $this->getAverageResponseTime(),
            // Assuming START_TIME is defined globally (e.g., in index.php)
            // 'app_uptime_seconds' => time() - START_TIME,
        ];
    }

    private function getHttpRequestsTotal(): int
    {
        return 12345;
    }

    private function getAverageResponseTime(): float
    {
        return round(mt_rand(50, 500) / 1000, 3);
    }
}
