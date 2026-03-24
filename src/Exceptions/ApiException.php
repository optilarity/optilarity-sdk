<?php

namespace Optilarity\Sdk\Exceptions;

class ApiException extends \RuntimeException
{
    protected int $statusCode;
    protected array $response;

    public function __construct(string $message = '', int $statusCode = 0, array $response = [], \Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->response   = $response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
