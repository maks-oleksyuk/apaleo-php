<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoClientException;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Exception\ApaleoValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/** Sends a resource request: adds auth, executes it, maps errors, decodes JSON. */
final readonly class RequestPipeline
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private TokenProvider $tokenProvider,
        private string $baseUri = 'https://api.apaleo.com',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function send(Request $apaleoRequest): array
    {
        $uri = $this->baseUri.$apaleoRequest->endpoint();
        $query = $apaleoRequest->query();
        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        $psrRequest = $this->requestFactory
            ->createRequest($apaleoRequest->method()->value, $uri)
            ->withHeader('Authorization', 'Bearer '.$this->tokenProvider->getToken()->value)
            ->withHeader('Accept', 'application/json')
        ;

        $body = $apaleoRequest->body();
        if ($body !== null) {
            $psrRequest = $psrRequest
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR)))
            ;
        }

        try {
            $response = $this->httpClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $clientException) {
            throw new ApaleoTransportException('Failed to reach Apaleo API: '.$clientException->getMessage(), $clientException->getCode(), previous: $clientException);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        $decoded = $body === '' ? [] : json_decode($body, true);

        /** @var array<string, mixed> $data */
        $data = \is_array($decoded) ? $decoded : [];

        if ($status >= 400) {
            throw $this->mapError($status, $data, $response->getHeaderLine('Retry-After'));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapError(int $status, array $data, string $retryAfter): ApaleoClientException|ApaleoServerException
    {
        $detail = $data['detail'] ?? $data['title'] ?? null;
        $message = \is_string($detail) ? $detail : "Apaleo API error (HTTP {$status})";
        $type = \is_string($data['type'] ?? null) ? $data['type'] : null;

        return match (true) {
            $status === 404 => new ApaleoNotFoundException($message, $status, $type, $data),
            $status === 429 => new ApaleoRateLimitException(
                message: $message,
                statusCode: $status,
                retryAfterSeconds: $retryAfter !== '' ? (int) $retryAfter : null,
                apaleoErrorType: $type,
                rawResponse: $data,
            ),
            $status === 401 || $status === 403 => new ApaleoAuthException($message, $status, $type, $data),
            $status === 400 || $status === 422 => new ApaleoValidationException($message, $status, $type, $data),
            $status >= 500 => new ApaleoServerException($message, $status, $type, $data),
            default => new ApaleoClientException($message, $status, $type, $data),
        };
    }
}
