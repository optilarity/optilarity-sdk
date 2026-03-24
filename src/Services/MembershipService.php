<?php

namespace Optilarity\Sdk\Services;

use Optilarity\Sdk\Contracts\ClientInterface;

/**
 * Membership / OAuth2 Service
 *
 * Usage:
 *   $url = $sdk->membership()->authorizeUrl($clientId, $redirectUri, $scopes);
 *   $token = $sdk->membership()->exchangeCode($clientId, $clientSecret, $code, $redirectUri);
 *   $plan  = $sdk->membership()->plan($accessToken);
 */
class MembershipService
{
    public function __construct(protected ClientInterface $client) {}

    /**
     * Build the OAuth2 authorization URL for redirecting the user.
     */
    public function authorizeUrl(
        string $clientId,
        string $redirectUri,
        array  $scopes = ['membership:read'],
        string $state  = ''
    ): string {
        $params = [
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(' ', $scopes),
            'state'         => $state ?: bin2hex(random_bytes(16)),
        ];

        // Build against the client's base URL
        $baseUrl = method_exists($this->client, 'getBaseUrl')
            ? $this->client->getBaseUrl()
            : '';

        return rtrim($baseUrl, '/') . '/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange an authorization code for an access token.
     */
    public function exchangeCode(
        string $clientId,
        string $clientSecret,
        string $code,
        string $redirectUri
    ): array {
        return $this->client->post('oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ]);
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshToken(
        string $clientId,
        string $clientSecret,
        string $refreshToken
    ): array {
        return $this->client->post('oauth/token', [
            'grant_type'    => 'refresh_token',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Get the current membership plan for the authenticated user.
     */
    public function plan(string $accessToken): array
    {
        return $this->client->withToken($accessToken)->get('api/membership/plan');
    }

    /**
     * Revoke an access token (logout).
     */
    public function revoke(string $clientId, string $clientSecret, string $token): array
    {
        return $this->client->post('oauth/revoke', [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'token'         => $token,
        ]);
    }
}
