<?php

namespace Optilarity\Sdk\Contracts;

interface ServiceInterface
{
    /**
     * Perform a GET request against the Optilarity API.
     *
     * @param string $endpoint
     * @param array  $params
     * @return array
     */
    public function get(string $endpoint, array $params = []): array;

    /**
     * Perform a POST request against the Optilarity API.
     *
     * @param string $endpoint
     * @param array  $payload
     * @return array
     */
    public function post(string $endpoint, array $payload = []): array;
}
