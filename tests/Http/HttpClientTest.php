<?php

namespace Optilarity\Sdk\Tests\Http;

use PHPUnit\Framework\TestCase;
use Optilarity\Sdk\Http\HttpClient;
use Optilarity\Sdk\Exceptions\ApiException;
use Optilarity\Sdk\Exceptions\AuthException;

/**
 * Unit tests for HttpClient.
 *
 * We subclass HttpClient and override `dispatch` to inject canned responses
 * without making real HTTP calls.
 */
class HttpClientTest extends TestCase
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Build a stub that always returns the given $body + $statusCode. */
    private function makeStub(array $body, int $status = 200): HttpClient
    {
        return new class($body, $status) extends HttpClient {
            public function __construct(
                private array $stubbedBody,
                private int   $stubbedStatus
            ) {
                parent::__construct('https://api.example.test');
            }

            protected function dispatch(string $method, string $url, array $body = []): array
            {
                return $this->parse(json_encode($this->stubbedBody), $this->stubbedStatus);
            }
        };
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_getBaseUrl_returns_configured_url(): void
    {
        $client = new HttpClient('https://api.optilarity.top');
        $this->assertSame('https://api.optilarity.top', $client->getBaseUrl());
    }

    public function test_withToken_returns_new_instance(): void
    {
        $client = new HttpClient('https://api.example.test');
        $cloned = $client->withToken('my-token');

        $this->assertNotSame($client, $cloned);
    }

    public function test_get_returns_decoded_json_on_success(): void
    {
        $stub     = $this->makeStub(['success' => true, 'data' => 'ok']);
        $response = $stub->get('/api/foo');

        $this->assertTrue($response['success']);
        $this->assertSame('ok', $response['data']);
    }

    public function test_post_returns_decoded_json_on_success(): void
    {
        $stub     = $this->makeStub(['success' => true, 'token' => 'abc123']);
        $response = $stub->post('/api/auth', ['key' => 'value']);

        $this->assertSame('abc123', $response['token']);
    }

    public function test_throws_api_exception_on_4xx(): void
    {
        $this->expectException(ApiException::class);
        $stub = $this->makeStub(['message' => 'Bad Request'], 400);
        $stub->get('/api/foo');
    }

    public function test_throws_auth_exception_on_401(): void
    {
        $this->expectException(AuthException::class);
        $stub = $this->makeStub(['message' => 'Unauthorized'], 401);
        $stub->get('/api/protected');
    }

    public function test_throws_auth_exception_on_403(): void
    {
        $this->expectException(AuthException::class);
        $stub = $this->makeStub(['message' => 'Forbidden'], 403);
        $stub->get('/api/admin-only');
    }

    public function test_api_exception_carries_status_code(): void
    {
        try {
            $stub = $this->makeStub(['message' => 'Not Found'], 404);
            $stub->get('/api/missing');
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $this->assertSame('Not Found', $e->getMessage());
        }
    }

    public function test_api_exception_carries_response_body(): void
    {
        try {
            $stub = $this->makeStub(['message' => 'Err', 'code' => 'INVALID_KEY'], 422);
            $stub->post('/api/activate');
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $e) {
            $this->assertSame('INVALID_KEY', $e->getResponse()['code']);
        }
    }
}
