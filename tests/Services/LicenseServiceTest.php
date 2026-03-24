<?php

namespace Optilarity\Sdk\Tests\Services;

use PHPUnit\Framework\TestCase;
use Optilarity\Sdk\Services\LicenseService;
use Optilarity\Sdk\Contracts\ClientInterface;

class LicenseServiceTest extends TestCase
{
    private $client;
    private $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->service = new LicenseService($this->client);
    }

    public function test_activate_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('api/license/activate', [
                'license_key' => 'KEY-123',
                'email' => 'test@example.com',
                'domain' => 'example.com'
            ])
            ->willReturn(['success' => true]);

        $result = $this->service->activate('KEY-123', 'test@example.com', 'example.com');
        $this->assertTrue($result['success']);
    }

    public function test_ping_with_token_attaches_auth_header(): void
    {
        // withToken returns a new instance of the client (fluent)
        $this->client->expects($this->once())
            ->method('withToken')
            ->with('fake-token')
            ->willReturn($this->client);

        $this->client->expects($this->once())
            ->method('get')
            ->with('api/license/ping')
            ->willReturn(['success' => true]);

        $this->service->ping('fake-token');
    }
}
