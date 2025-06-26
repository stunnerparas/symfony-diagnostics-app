<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Integration tests for SystemDiagnosticsController.
 * Covers API endpoint behavior, validation, and security.
 */
class SystemDiagnosticsControllerTest extends WebTestCase
{
    private const API_ENDPOINT = '/api/system/diagnostics';
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure this token matches the one configured in .env.test.local (or .env)
        // For tests, it's safer to use a distinct test token if possible.
        $this->authToken = $_ENV['APP_API_TOKEN'] ?? 'test_secret_token';
    }

    public function testDiagnosticsEndpointWithValidRequestAndAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode(['include' => ['php', 'symfony']])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('diagnostics', $responseData);
        $this->assertArrayHasKey('metadata', $responseData);
        $this->assertArrayHasKey('executionTime', $responseData);

        $this->assertArrayHasKey('php', $responseData['diagnostics']);
        $this->assertArrayHasKey('symfony', $responseData['diagnostics']);
        $this->assertArrayNotHasKey('system', $responseData['diagnostics']);
    }

    public function testDiagnosticsEndpointWithInvalidProviderAndAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode(['include' => ['invalid_provider']])
        );

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        // --- FIX: Updated expected message for choice validation ---
        $this->assertEquals('Invalid request', $responseData['error']);
        $this->assertStringContainsString('The value you selected is not a valid choice.', $responseData['message']);
    }

    public function testDiagnosticsEndpointWithLevelBasicAndAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode(['level' => 'basic'])
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('php', $responseData['diagnostics']);
        $this->assertArrayHasKey('symfony', $responseData['diagnostics']);
        $this->assertArrayNotHasKey('system', $responseData['diagnostics']);
    }

    public function testDiagnosticsEndpointWithoutAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['include' => ['php']])
        );

        $this->assertResponseStatusCodeSame(401);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Authentication Required.', $responseData['message']);
    }

    public function testDiagnosticsEndpointWithInvalidAuthToken(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token_123',
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode(['include' => ['php']])
        );

        $this->assertResponseStatusCodeSame(401);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Invalid API token.', $responseData['message']);
    }

    public function testLoginCheckWithValidCredentials(): void
    {
        $client = static::createClient();

        $loginPassword = 'paras_for_pimcore_2025';

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'api_user',
                'password' => $loginPassword
            ])
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertEquals($this->authToken, $responseData['token']);
        $this->assertEquals('Authentication successful!', $responseData['message']);
    }

    public function testLoginCheckWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'wrong_user',
                'password' => 'wrong_password'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Invalid credentials.', $responseData['message']);
    }
}
