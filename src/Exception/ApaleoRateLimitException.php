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
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $apaleoErrorType, $rawResponse, $previous);
    }

    /**
     * RFC 7231: Retry-After is either delta-seconds ("120") or an HTTP-date
     * ("Wed, 21 Oct 2026 07:28:00 GMT") — both appear in the wild.
     */
    public static function parseRetryAfter(string $retryAfter): ?int
    {
        if ($retryAfter === '') {
            return null;
        }

        if (filter_var($retryAfter, FILTER_VALIDATE_INT) !== false) {
            return (int) $retryAfter;
        }

        // Equivalent to the now-deprecated DateTimeInterface::RFC7231 constant, spelled out
        // literally so PHP 8.5+ doesn't warn about its GMT-only timezone assumption.
        $date = \DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', $retryAfter);
        if ($date === false) {
            return null;
        }

        return max(0, $date->getTimestamp() - time());
    }
}
