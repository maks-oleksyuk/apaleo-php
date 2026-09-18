<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

abstract class ApaleoException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?string $apaleoErrorType = null,
        public readonly array $rawResponse = [],
    ) {
        parent::__construct($message);
    }
}
