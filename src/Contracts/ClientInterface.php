<?php

namespace Optilarity\Sdk\Contracts;

interface ClientInterface
{
    public function get(string $endpoint, array $params = []): array;

    public function post(string $endpoint, array $payload = []): array;

    public function withToken(string $token): static;
}
