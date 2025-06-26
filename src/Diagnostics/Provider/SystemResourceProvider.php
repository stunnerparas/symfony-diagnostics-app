<?php

namespace App\Diagnostics\Provider;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**

 * This provider collects information about the underlying system's resource consumption, such as PHP memory usage, CPU load (on Linux), and disk space.

 */
#[AsTaggedItem('diagnostics.provider')]
class SystemResourceProvider extends AbstractDiagnosticsProvider
{
    public function getKey(): string
    {
        return 'system';
    }

    public function getDiagnostics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cpu_load' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'disk_free' => disk_free_space('/'),
            'uptime' => $this->getSystemUptime(),
        ];
    }

    private function getSystemUptime(): ?float
    {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return (float) explode(' ', $uptime)[0];
        }
        return null;
    }
}
