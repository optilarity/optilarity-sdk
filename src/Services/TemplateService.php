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
     * List available templates, optionally filtered by category.
     */
    public function list(string $category = '', int $page = 1, int $perPage = 20): array
    {
        $params = ['page' => $page, 'per_page' => $perPage];
        if ($category) {
            $params['category'] = $category;
        }
        return $this->client->get('api/templates', $params);
    }

    /**
     * Get a single template's metadata.
     */
    public function get(string $templateId): array
    {
        return $this->client->get("api/templates/{$templateId}");
    }

    /**
     * Download a template (requires membership token).
     */
    public function download(string $accessToken, string $templateId): array
    {
        return $this->client->withToken($accessToken)->get("api/templates/{$templateId}/download");
    }
}
