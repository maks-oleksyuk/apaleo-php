<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

class ApaleoValidationException extends ApaleoClientException
{
    /**
     * @param array<string, mixed> $rawResponse
     * @param list<string>         $messages    Apaleo's individual validation messages, e.g. "Code: The Code field is required."
     */
    public function __construct(
        string $message,
        int $statusCode,
        ?string $apaleoErrorType = null,
        array $rawResponse = [],
        public readonly array $messages = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $apaleoErrorType, $rawResponse, $previous);
    }
}
