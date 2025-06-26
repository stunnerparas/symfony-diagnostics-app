<?php

namespace App\Tests\Diagnostics\Provider;

use App\Diagnostics\Provider\PhpDiagnosticsProvider;
use PHPUnit\Framework\TestCase;


class PhpDiagnosticsProviderTest extends TestCase
{
    private PhpDiagnosticsProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new PhpDiagnosticsProvider();
    }

    /**
     * Test getKey method returns 'php'.
     */
    public function testGetKey(): void
    {
        $this->assertEquals('php', $this->provider->getKey());
    }

    /**
     * Test getDiagnostics returns expected array keys/types.
     */
    public function testGetDiagnostics(): void
    {
        $diagnostics = $this->provider->getDiagnostics();

        $this->assertIsArray($diagnostics);
        $this->assertArrayHasKey('version', $diagnostics);
        $this->assertArrayHasKey('sapi', $diagnostics);
        $this->assertArrayHasKey('extensions', $diagnostics);
        $this->assertArrayHasKey('memory_limit', $diagnostics);
        $this->assertArrayHasKey('max_execution_time', $diagnostics);
        $this->assertArrayHasKey('display_errors', $diagnostics);

        $this->assertEquals(PHP_VERSION, $diagnostics['version']);
        $this->assertEquals(PHP_SAPI, $diagnostics['sapi']);
        $this->assertIsArray($diagnostics['extensions']);
    }

    /**
     * Test provider is enabled by default.
     */
    public function testIsEnabledByDefault(): void
    {
        $this->assertTrue($this->provider->isEnabled());
    }

    /**
     * Test provider can be disabled.
     */
    public function testCanBeDisabled(): void
    {
        $this->provider->setEnabled(false);
        $this->assertFalse($this->provider->isEnabled());
    }
}
