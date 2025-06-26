<?php

namespace App\Tests\Service;

use App\Diagnostics\Exception\InvalidProviderException;
use App\Diagnostics\Provider\DiagnosticsProviderInterface;
use App\Service\SystemDiagnosticsCollector;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SystemDiagnosticsCollector.
 */
class SystemDiagnosticsCollectorTest extends TestCase
{
    /**
     * Test collecting all enabled providers.
     */
    public function testCollectAllProviders(): void
    {
        $provider1 = $this->createMockProvider('provider1', ['data1' => 'value1'], true);
        $provider2 = $this->createMockProvider('provider2', ['data2' => 'value2'], true);

        $collector = new SystemDiagnosticsCollector([$provider1, $provider2]);
        $result = $collector->collect();

        $this->assertArrayHasKey('provider1', $result);
        $this->assertArrayHasKey('provider2', $result);
        $this->assertEquals(['data1' => 'value1'], $result['provider1']);
        $this->assertEquals(['data2' => 'value2'], $result['provider2']);
    }

    /**
     * Test collecting specific providers.
     */
    public function testCollectSpecificProviders(): void
    {
        $provider1 = $this->createMockProvider('provider1', ['data1' => 'value1'], true);
        $provider2 = $this->createMockProvider('provider2', ['data2' => 'value2'], true);

        $collector = new SystemDiagnosticsCollector([$provider1, $provider2]);
        $result = $collector->collect(['provider1']);

        $this->assertArrayHasKey('provider1', $result);
        $this->assertArrayNotHasKey('provider2', $result);
        $this->assertEquals(['data1' => 'value1'], $result['provider1']);
    }

    /**
     * Test InvalidProviderException for unknown provider.
     */
    public function testInvalidProviderThrowsException(): void
    {
        $provider = $this->createMockProvider('valid_provider', [], true);
        $collector = new SystemDiagnosticsCollector([$provider]);

        $this->expectException(InvalidProviderException::class);
        $this->expectExceptionMessage('Invalid diagnostics provider: "invalid_provider"');

        $collector->collect(['invalid_provider']);
    }

    /**
     * Test disabled providers are skipped.
     */
    public function testDisabledProviderIsSkipped(): void
    {
        $enabledProvider = $this->createMockProvider('enabled', ['data' => 'enabled_value'], true);
        $disabledProvider = $this->createMockProvider('disabled', ['data' => 'disabled_value'], false);

        $collector = new SystemDiagnosticsCollector([$enabledProvider, $disabledProvider]);
        $result = $collector->collect();

        $this->assertArrayHasKey('enabled', $result);
        $this->assertArrayNotHasKey('disabled', $result);
    }

    /**
     * Test provider errors are caught and reported.
     */
    public function testProviderErrorsAreCaught(): void
    {
        $failingProvider = $this->createMock(DiagnosticsProviderInterface::class);
        $failingProvider->method('getKey')->willReturn('failing');
        $failingProvider->method('isEnabled')->willReturn(true);
        $failingProvider->method('getDiagnostics')->willThrowException(new \RuntimeException('Something went wrong!'));

        $collector = new SystemDiagnosticsCollector([$failingProvider]);
        $result = $collector->collect();

        $this->assertArrayHasKey('failing', $result);
        $this->assertArrayHasKey('error', $result['failing']);
        $this->assertStringContainsString('Diagnostics error in provider "failing": Something went wrong!', $result['failing']['error']);
    }

    /**
     * Helper to create a mock DiagnosticsProviderInterface.
     */
    private function createMockProvider(string $key, array $diagnostics, bool $isEnabled): DiagnosticsProviderInterface
    {
        $provider = $this->createMock(DiagnosticsProviderInterface::class);
        $provider->method('getKey')->willReturn($key);
        $provider->method('getDiagnostics')->willReturn($diagnostics);
        $provider->method('isEnabled')->willReturn($isEnabled);
        return $provider;
    }
}
