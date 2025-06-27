<?php

namespace App\Tests\Service;

use App\Diagnostics\Exception\InvalidProviderException;
use App\Service\DiagnosticsProviderInterface;
use App\Service\SystemDiagnosticsCollector;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for SystemDiagnosticsCollector.
 */
class SystemDiagnosticsCollectorTest extends TestCase
{
    private LoggerInterface $mockLogger;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for LoggerInterface
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        // should NOT have `willReturn(null)` or `willReturnSelf()`.
        // PHPUnit automatically handles void methods correctly without explicit return stubbing.
        // We can just define the method if we need to set expectations on it later.
    }

    /**
     * Test collecting all enabled providers.
     */
    public function testCollectAllProviders(): void
    {
        // Use 'php' and 'symfony' as keys to match expected assertions in the test
        $provider1 = $this->createMockProvider('php', ['version' => '8.3'], true);
        $provider2 = $this->createMockProvider('symfony', ['env' => 'test'], true);

        $collector = new SystemDiagnosticsCollector([$provider1, $provider2], $this->mockLogger);

        $diagnostics = $collector->collect();

        $this->assertArrayHasKey('php', $diagnostics);
        $this->assertArrayHasKey('symfony', $diagnostics);
        $this->assertEquals(['version' => '8.3'], $diagnostics['php']);
        $this->assertEquals(['env' => 'test'], $diagnostics['symfony']);
    }

    /**
     * Test collecting specific providers.
     */
    public function testCollectSpecificProviders(): void
    {
        $provider1 = $this->createMockProvider('provider1', ['data1' => 'value1'], true);
        $provider2 = $this->createMockProvider('provider2', ['data2' => 'value2'], true);

        $collector = new SystemDiagnosticsCollector([$provider1, $provider2], $this->mockLogger);

        $diagnostics = $collector->collect(['provider1']);

        $this->assertArrayHasKey('provider1', $diagnostics);
        $this->assertArrayNotHasKey('provider2', $diagnostics);
        $this->assertEquals(['data1' => 'value1'], $diagnostics['provider1']);
    }

    /**
     * Test InvalidProviderException for unknown provider.
     */
    public function testInvalidProviderThrowsException(): void
    {
        $provider = $this->createMockProvider('valid_provider', [], true);
        $collector = new SystemDiagnosticsCollector([$provider], $this->mockLogger);

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

        $collector = new SystemDiagnosticsCollector([$enabledProvider, $disabledProvider], $this->mockLogger);

        $diagnostics = $collector->collect();

        $this->assertArrayHasKey('enabled', $diagnostics);
        $this->assertArrayNotHasKey('disabled', $diagnostics);
    }

    /**
     * Test provider errors are caught and reported.
     */
    public function testProviderErrorsAreCaught(): void
    {
        $failingProvider = $this->createMock(DiagnosticsProviderInterface::class);
        $failingProvider->method('getKey')->willReturn('failing');
        $failingProvider->method('isEnabled')->willReturn(true);
        // Simulate an exception being thrown by getDiagnostics
        $failingProvider->method('getDiagnostics')
            ->willThrowException(new \RuntimeException('Something went wrong!'));

        // Expect the logger's error method to be called at least once with any parameters.
        // This is the most lenient assertion to confirm the method is indeed called.
        $this->mockLogger->expects($this->atLeastOnce())
            ->method('error')
            ->withAnyParameters();

        $collector = new SystemDiagnosticsCollector([$failingProvider], $this->mockLogger);
        $result = $collector->collect();

        $this->assertArrayHasKey('failing', $result);
        $this->assertArrayHasKey('error', $result['failing']);
        $this->assertStringContainsString('Something went wrong!', $result['failing']['error']);
    }

    /**
     * Helper to create a mock DiagnosticsProviderInterface.
     * @param string $key The key for the provider.
     * @param array<string, mixed> $diagnostics The diagnostic data to return.
     * @param bool $isEnabled Whether the provider is enabled.
     * @return DiagnosticsProviderInterface The created mock provider.
     */
    private function createMockProvider(string $key, array $diagnostics, bool $isEnabled): DiagnosticsProviderInterface
    {
        $provider = $this->createMock(DiagnosticsProviderInterface::class);
        $provider->method('getKey')->willReturn($key);
        $provider->method('getDiagnostics')->willReturn($diagnostics);
        $provider->method('isEnabled')->willReturn($isEnabled);
        // Mock getPriority() as well, as the interface now requires it and it might be called.
        $provider->method('getPriority')->willReturn(0); // Default mock for getPriority
        return $provider;
    }
}
