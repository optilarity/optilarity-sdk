<?php

namespace Optilarity\Sdk\Http;

use Optilarity\Sdk\Contracts\ClientInterface;
use Optilarity\Sdk\Exceptions\ApiException;
use Optilarity\Sdk\Exceptions\AuthException;

/**
 * WordPress-compatible HTTP client using wp_remote_get / wp_remote_post.
 * Falls back to native cURL when WordPress functions are unavailable.
 */
class HttpClient implements ClientInterface
{
    protected string $baseUrl;
    protected string $token = '';
    protected int $timeout;

    public function __construct(string $baseUrl, int $timeout = 15)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // ─────────────────────────────────────────────────────────────
    // Builder
    // ─────────────────────────────────────────────────────────────

    public function withToken(string $token): static
    {
        $clone        = clone $this;
        $clone->token = $token;
        return $clone;
    }

    // ─────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────

    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->dispatch('GET', $url);
    }

    public function post(string $endpoint, array $payload = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->dispatch('POST', $url, $payload);
    }

    // ─────────────────────────────────────────────────────────────
    // Internals
    // ─────────────────────────────────────────────────────────────

    protected function buildUrl(string $endpoint, array $params = []): string
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    protected function buildHeaders(): array
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }
        return $headers;
    }

    protected function dispatch(string $method, string $url, array $body = []): array
    {
        if (function_exists('wp_remote_request')) {
            return $this->dispatchWordPress($method, $url, $body);
        }
        return $this->dispatchCurl($method, $url, $body);
    }

    // ── WordPress adapter ──────────────────────────────────────

    protected function dispatchWordPress(string $method, string $url, array $body = []): array
    {
        $args = [
            'method'  => $method,
            'timeout' => $this->timeout,
            'headers' => $this->buildHeaders(),
        ];

        if ($body) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message());
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        $rawBody    = wp_remote_retrieve_body($response);

        return $this->parse($rawBody, (int) $statusCode);
    }

    // ── cURL fallback ──────────────────────────────────────────

    protected function dispatchCurl(string $method, string $url, array $body = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headersToStrings());

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $rawBody    = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($rawBody === false) {
            throw new ApiException('cURL request failed.');
        }

        return $this->parse($rawBody, $statusCode);
    }

    protected function headersToStrings(): array
    {
        $out = [];
        foreach ($this->buildHeaders() as $key => $value) {
            $out[] = "{$key}: {$value}";
        }
        return $out;
    }

    // ── Response parser ────────────────────────────────────────

    protected function parse(string $rawBody, int $statusCode): array
    {
        $data = json_decode($rawBody, true) ?? [];

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthException($data['message'] ?? 'Unauthorized.', $statusCode, $data);
        }

        if ($statusCode >= 400) {
            throw new ApiException($data['message'] ?? 'API error.', $statusCode, $data);
        }

        return $data;
    }
}
