<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

class ApaleoRateLimitException extends ApaleoClientException
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(
        string $message,
        int $statusCode,
        public readonly ?int $retryAfterSeconds = null,
        ?string $apaleoErrorType = null,
        array $rawResponse = [],
    ) {
        parent::__construct($message, $statusCode, $apaleoErrorType, $rawResponse);
    }
}
