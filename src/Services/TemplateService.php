<?php

namespace Optilarity\Sdk\Services;

use Optilarity\Sdk\Contracts\ClientInterface;

/**
 * Template / Asset Catalog Service
 *
 * Usage:
 *   $templates = $sdk->templates()->list('landing-page');
 *   $asset     = $sdk->templates()->download($token, $templateId);
 */
class TemplateService
{
    public function __construct(protected ClientInterface $client) {}

    /**
     * List available items in the software catalog.
     */
    public function list(string $type = 'themes', int $page = 1, int $perPage = 20): array
    {
        $endpoint = match ($type) {
            'themes'  => 'api/catalog/themes',
            'plugins' => 'api/catalog/plugins',
            default   => 'api/catalog',
        };

        return $this->client->get($endpoint, [
            'page'     => $page,
            'per_page' => $perPage
        ]);
    }

    /**
     * Get a single catalog item's metadata.
     */
    public function get(string $itemId): array
    {
        return $this->client->get("api/catalog/{$itemId}");
    }

    /**
     * Securely download a catalog asset.
     */
    public function download(string $accessToken, string $itemId): array
    {
        // On the modular backend, downloads are channeled through this endpoint
        return $this->client->withToken($accessToken)
            ->get("api/catalog/{$itemId}/download");
    }
}
