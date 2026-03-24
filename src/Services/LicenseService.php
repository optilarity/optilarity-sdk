<?php

namespace Optilarity\Sdk\Services;

use Optilarity\Sdk\Contracts\ClientInterface;

/**
 * License Service
 *
 * Usage:
 *   $sdk = OptilaritySdk::make('https://api.optilarity.top');
 *   $sdk->license()->activate('LICENSE-KEY', 'email@example.com');
 *   $sdk->license()->ping();
 */
class LicenseService
{
    public function __construct(protected ClientInterface $client) {}

    /**
     * Activate/Verify a license key for the current domain.
     */
    public function activate(string $licenseKey, string $email, string $domain = ''): array
    {
        return $this->client->post('api/license/verify', [
            'license_key' => $licenseKey,
            'email'       => $email,
            'domain'      => $domain ?: $this->currentDomain(),
        ]);
    }

    /**
     * Deactivate the current license.
     */
    public function deactivate(string $licenseKey, string $domain = ''): array
    {
        return $this->client->post('api/license/deactivate', [
            'license_key' => $licenseKey,
            'domain'      => $domain ?: $this->currentDomain(),
        ]);
    }

    /**
     * Ping/heartbeat – verifies the stored token is still valid.
     */
    public function ping(string $licenseKey): array
    {
        return $this->client->post('api/theme/ping', [
            'license_key' => $licenseKey,
            'domain'      => $this->currentDomain(),
        ]);
    }

    /**
     * Check for available product updates.
     */
    public function checkUpdates(): array
    {
        return $this->client->get('api/theme/latest');
    }

    /**
     * Get activation logs for a license key.
     */
    public function logs(string $licenseKey = '', int $limit = 20): array
    {
        $params = ['limit' => $limit];
        if ($licenseKey) {
            $params['license_key'] = $licenseKey;
        }
        return $this->client->get('api/licenses/logs', $params);
    }

    /**
     * Get license usage statistics.
     */
    public function stats(): array
    {
        return $this->client->get('api/licenses/stats');
    }

    // ─────────────────────────────────────────────────────────────

    protected function currentDomain(): string
    {
        if (function_exists('get_site_url')) {
            return parse_url(get_site_url(), PHP_URL_HOST) ?? '';
        }
        return $_SERVER['HTTP_HOST'] ?? '';
    }
}
