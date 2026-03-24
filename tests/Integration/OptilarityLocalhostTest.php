<?php

namespace Optilarity\Sdk\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Optilarity\Sdk\OptilaritySdk;
use Optilarity\Sdk\Exceptions\ApiException;

/**
 * Integration Test against LOCAL development server.
 * 
 * To run this:
 * 1. Ensure http://optilarity.localhost is responding.
 * 2. ./vendor/bin/phpunit tests/Integration/OptilarityLocalhostTest.php
 */
class OptilarityLocalhostTest extends TestCase
{
    protected OptilaritySdk $sdk;

    protected function setUp(): void
    {
        // POINT TO LOCALHOST
        $this->sdk = OptilaritySdk::make('http://optilarity.localhost');
    }

    /**
     * Test a real flow: License Activation
     */
    public function test_license_activation_on_localhost(): void
    {
        try {
            $result = $this->sdk->license()->activate(
                'TEST-KEY-12345', 
                'dev@jankx.pro',
                'jankx.localhost'
            );

            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            echo "\n[INTEGRATION] License activation response: ";
            print_r($result);

        } catch (ApiException $e) {
            // If the server is down or returns 404, we catch it but fail the test with a clear reason
            $this->fail("Failed to connect to optilarity.localhost. Status: " . $e->getStatusCode());
        } catch (\Exception $e) {
            $this->markTestSkipped("optilarity.localhost is unreachable. Ignoring integration test. (" . $e->getMessage() . ")");
        }
    }

    /**
     * Test a real flow: OAuth2 Authorize URL Generation
     */
    public function test_oauth_url_generation(): void
    {
        $url = $this->sdk->membership()->authorizeUrl(
            'jankx_client',
            'http://jankx.localhost/wp-admin/admin.php?page=jankx-membership'
        );

        $this->assertStringContainsString('optilarity.localhost', $url);
        $this->assertStringContainsString('client_id=jankx_client', $url);
        
        echo "\n[INTEGRATION] Generated OAuth URL: $url\n";
    }

    /**
     * Test a real flow: Catalog Fetching
     */
    public function test_catalog_fetch_on_localhost(): void
    {
        try {
            $templates = $this->sdk->templates()->list();
            $this->assertIsArray($templates);
            
            echo "\n[INTEGRATION] Template catalog count: " . count($templates) . "\n";
        } catch (\Exception $e) {
            $this->markTestSkipped("Template catalog failed on localhost: " . $e->getMessage());
        }
    }
}
