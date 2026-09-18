<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoClientException;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Exception\ApaleoValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
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
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function send(Request $apaleoRequest): array
    {
        $response = $this->execute($apaleoRequest, $this->tokenProvider->getToken());

        if ($response->getStatusCode() === 401) {
            $response = $this->execute($apaleoRequest, $this->tokenProvider->getToken(forceRefresh: true));
        }

        $status = $response->getStatusCode();
        $rawBody = (string) $response->getBody();
        $decoded = $rawBody === '' ? [] : json_decode($rawBody, true);

        if (!\is_array($decoded)) {
            throw new ApaleoUnexpectedResponseException("Apaleo API returned a non-JSON or malformed body (HTTP {$status}).");
        }

        /** @var array<string, mixed> $data */
        $data = $decoded;

        if ($status >= 400) {
            throw $this->mapError($status, $data, $response->getHeaderLine('Retry-After'));
        }

        return $data;
    }

    private function execute(Request $apaleoRequest, AccessToken $token): ResponseInterface
    {
        $uri = $this->baseUri.$apaleoRequest->endpoint();
        $query = $apaleoRequest->query();
        if ($query !== []) {
            $query = array_map(static fn (mixed $value): mixed => \is_bool($value) ? ($value ? 'true' : 'false') : $value, $query);
            $uri .= '?'.http_build_query($query);
        }

        $psrRequest = $this->requestFactory
            ->createRequest($apaleoRequest->method()->value, $uri)
            ->withHeader('Authorization', 'Bearer '.$token->value)
            ->withHeader('Accept', 'application/json')
        ;

        $body = $apaleoRequest->body();
        if ($body !== null) {
            try {
                $encodedBody = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (\JsonException $jsonException) {
                throw new ApaleoUnexpectedResponseException('Failed to encode request body as JSON: '.$jsonException->getMessage(), $jsonException->getCode(), previous: $jsonException);
            }

            $psrRequest = $psrRequest
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($encodedBody))
            ;
        }

        try {
            return $this->httpClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $clientException) {
            throw new ApaleoTransportException('Failed to reach Apaleo API: '.$clientException->getMessage(), $clientException->getCode(), previous: $clientException);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapError(int $status, array $data, string $retryAfter): ApaleoClientException|ApaleoServerException
    {
        $detail = $data['detail'] ?? $data['title'] ?? null;
        $message = \is_string($detail) ? $detail : "Apaleo API error (HTTP {$status})";
        $type = \is_string($data['type'] ?? null) ? $data['type'] : null;
        $messages = $this->extractMessages($data);

        if ($messages !== [] && ($status === 400 || $status === 422)) {
            $message = $messages[0];
        }

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
            $status === 400 || $status === 422 => new ApaleoValidationException($message, $status, $type, $data, $messages),
            $status >= 500 => new ApaleoServerException($message, $status, $type, $data),
            default => new ApaleoClientException($message, $status, $type, $data),
        };
    }

    /**
     * Apaleo reports validation/business-rule failures as a flat list of human-readable strings
     * under `messages`, e.g. "Code: The Code field is required." (no structured per-field object).
     *
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    private function extractMessages(array $data): array
    {
        $messages = $data['messages'] ?? null;

        return \is_array($messages) ? array_values(array_filter($messages, \is_string(...))) : [];
    }
}
