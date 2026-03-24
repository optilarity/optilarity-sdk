<?php

namespace Optilarity\Sdk;

use Optilarity\Sdk\Http\HttpClient;
use Optilarity\Sdk\Contracts\ClientInterface;
use Optilarity\Sdk\Services\LicenseService;
use Optilarity\Sdk\Services\MembershipService;
use Optilarity\Sdk\Services\TemplateService;

/**
 * Optilarity SDK – Fluent Entry Point
 *
 * ─── Quick Start ──────────────────────────────────────────────────────────────
 *
 *   // Instantiate once (e.g. in a service provider)
 *   $sdk = OptilaritySdk::make('https://api.optilarity.top');
 *
 *   // License operations
 *   $response = $sdk->license()->activate('XXXX-KEY', 'email@example.com');
 *
 *   // Membership / OAuth2
 *   $url  = $sdk->membership()->authorizeUrl($clientId, $redirectUri);
 *   $data = $sdk->membership()->plan($accessToken);
 *
 *   // Templates
 *   $list = $sdk->templates()->list('landing-page');
 *
 * ─── Singleton (optional) ────────────────────────────────────────────────────
 *
 *   OptilaritySdk::configure('https://api.optilarity.top');
 *   $plan = OptilaritySdk::getInstance()->membership()->plan($token);
 *
 * ─────────────────────────────────────────────────────────────────────────────
 */
class OptilaritySdk
{
    private static ?self $instance = null;

    // ─── Lazy-loaded services ─────────────────────────────────────────────────

    private ?LicenseService    $licenseService    = null;
    private ?MembershipService $membershipService = null;
    private ?TemplateService   $templateService   = null;

    // ─── Constructor ──────────────────────────────────────────────────────────

    final public function __construct(protected ClientInterface $client) {}

    // ─── Factory / Singleton API ──────────────────────────────────────────────

    /**
     * Create a new SDK instance with the default WordPress-aware HTTP client.
     *
     * @param string $baseUrl   e.g. 'https://api.optilarity.top'
     * @param int    $timeout   HTTP request timeout in seconds
     */
    public static function make(string $baseUrl, int $timeout = 15): static
    {
        return new static(new HttpClient($baseUrl, $timeout));
    }

    /**
     * Create an SDK instance with a custom client (e.g. a mock in tests).
     */
    public static function makeWith(ClientInterface $client): static
    {
        return new static($client);
    }

    /**
     * Configure the shared singleton instance.
     */
    public static function configure(string $baseUrl, int $timeout = 15): void
    {
        static::$instance = static::make($baseUrl, $timeout);
    }

    /**
     * Retrieve the shared singleton (must call configure() first).
     *
     * @throws \LogicException
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            throw new \LogicException('OptilaritySdk has not been configured. Call OptilaritySdk::configure($baseUrl) first.');
        }
        return static::$instance;
    }

    // ─── Service Accessors (lazy, immutable) ──────────────────────────────────

    /**
     * @return LicenseService
     */
    public function license(): LicenseService
    {
        return $this->licenseService ??= new LicenseService($this->client);
    }

    /**
     * @return MembershipService
     */
    public function membership(): MembershipService
    {
        return $this->membershipService ??= new MembershipService($this->client);
    }

    /**
     * @return TemplateService
     */
    public function templates(): TemplateService
    {
        return $this->templateService ??= new TemplateService($this->client);
    }

    /**
     * Return the underlying HTTP client (for custom calls / debugging).
     */
    public function client(): ClientInterface
    {
        return $this->client;
    }
}
