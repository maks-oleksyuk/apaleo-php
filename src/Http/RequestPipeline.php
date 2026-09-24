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
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;

final readonly class RequestPipeline
{
    public const string DEFAULT_BASE_URI = 'https://api.apaleo.com';

    private string $baseUri;

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private TokenProvider $tokenProvider,
        string $baseUri = self::DEFAULT_BASE_URI,
        private ?SymfonyHttpClientInterface $asyncHttpClient = null,
    ) {
        $this->baseUri = rtrim($baseUri, '/');
    }

    /**
     * @return array<string, mixed>
     */
    public function send(Request $apaleoRequest): array
    {
        $response = $this->executeWithRetriedAuth($apaleoRequest);

        return $this->handleResponse($response->getStatusCode(), (string) $response->getBody(), $response->getHeaderLine('Retry-After'));
    }

    /**
     * Same as send(), but returns the body as-is instead of decoding it: for binary endpoints like
     * invoice PDFs. Errors still come back as JSON and are mapped to the usual exceptions.
     */
    public function sendRaw(Request $apaleoRequest): string
    {
        $response = $this->executeWithRetriedAuth($apaleoRequest);
        $rawBody = (string) $response->getBody();

        if ($response->getStatusCode() >= 400) {
            $this->handleResponse($response->getStatusCode(), $rawBody, $response->getHeaderLine('Retry-After'));
        }

        return $rawBody;
    }

    /**
     * All-or-nothing: the first failed response throws, and the other results are lost.
     *
     * Sends several requests concurrently via $asyncHttpClient, if one was given; otherwise falls
     * back to send()-ing them one by one. $httpClient is never used here: a PSR-18-wrapped
     * Symfony client can't be unwrapped back into its concurrency-capable form.
     *
     * @param list<Request> $requests
     *
     * @return list<array<string, mixed>> decoded response bodies, in the same order as $requests
     */
    public function sendMany(array $requests): array
    {
        if ($requests === []) {
            return [];
        }

        $asyncHttpClient = $this->asyncHttpClient;
        if (!$asyncHttpClient instanceof SymfonyHttpClientInterface) {
            return array_map($this->send(...), $requests);
        }

        $token = $this->tokenProvider->getToken();
        $responses = array_map(fn (Request $request): SymfonyResponseInterface => $this->executeAsync($request, $asyncHttpClient, $token), $requests);

        // Symfony multiplexes pending responses together the first time one is read, so this
        // loop resolves all of them concurrently despite reading them one at a time.
        $retryIndexes = [];
        foreach ($responses as $i => $response) {
            if ($this->statusCode($response) === 401) {
                $retryIndexes[] = $i;
            }
        }

        if ($retryIndexes !== []) {
            $freshToken = $this->tokenProvider->getToken(forceRefresh: true);
            $retried = array_map(fn (int $i): SymfonyResponseInterface => $this->executeAsync($requests[$i], $asyncHttpClient, $freshToken), $retryIndexes);
            foreach ($retryIndexes as $j => $i) {
                $responses[$i] = $retried[$j];
            }
        }

        return array_values(array_map(
            fn (SymfonyResponseInterface $response): array => $this->handleResponse(
                $this->statusCode($response),
                $this->content($response),
                $this->headerLine($response, 'retry-after'),
            ),
            $responses,
        ));
    }

    private function executeWithRetriedAuth(Request $apaleoRequest): ResponseInterface
    {
        $response = $this->execute($apaleoRequest, $this->tokenProvider->getToken());

        if ($response->getStatusCode() === 401) {
            return $this->execute($apaleoRequest, $this->tokenProvider->getToken(forceRefresh: true));
        }

        return $response;
    }

    private function statusCode(SymfonyResponseInterface $response): int
    {
        try {
            return $response->getStatusCode();
        } catch (\Throwable $throwable) {
            throw new ApaleoTransportException('Failed to reach Apaleo API: '.$throwable->getMessage(), $throwable->getCode(), previous: $throwable);
        }
    }

    private function content(SymfonyResponseInterface $response): string
    {
        try {
            return $response->getContent(false);
        } catch (\Throwable $throwable) {
            throw new ApaleoTransportException('Failed to reach Apaleo API: '.$throwable->getMessage(), $throwable->getCode(), previous: $throwable);
        }
    }

    private function headerLine(SymfonyResponseInterface $response, string $name): string
    {
        try {
            return $response->getHeaders(false)[$name][0] ?? '';
        } catch (\Throwable $throwable) {
            throw new ApaleoTransportException('Failed to reach Apaleo API: '.$throwable->getMessage(), $throwable->getCode(), previous: $throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function handleResponse(int $status, string $rawBody, string $retryAfter): array
    {
        $decoded = $rawBody === '' ? [] : json_decode($rawBody, true);

        // Checked before the JSON shape: gateways/CDNs answer 502/503/504 with HTML, and those
        // must still surface as ApaleoServerException so callers' retry logic catches them.
        if ($status >= 400) {
            /** @var array<string, mixed> $errorData */
            $errorData = \is_array($decoded) ? $decoded : [];

            throw $this->mapError($status, $errorData, $retryAfter, $rawBody);
        }

        if (!\is_array($decoded)) {
            throw new ApaleoUnexpectedResponseException("Apaleo API returned a non-JSON or malformed body (HTTP {$status}): ".$this->excerpt($rawBody));
        }

        // @phpstan-ignore return.type (a JSON object decodes to string keys; a top-level list isn't an Apaleo response shape)
        return $decoded;
    }

    private function execute(Request $apaleoRequest, AccessToken $token): ResponseInterface
    {
        $uri = $this->baseUri.$apaleoRequest->endpoint();
        $query = $this->query($apaleoRequest);
        if ($query !== []) {
            $uri .= '?'.http_build_query($query);
        }

        $psrRequest = $this->requestFactory
            ->createRequest($apaleoRequest->method()->value, $uri)
            ->withHeader('Authorization', 'Bearer '.$token->value)
            ->withHeader('Accept', $apaleoRequest->accept())
        ;

        foreach ($this->headers($apaleoRequest) as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        $body = $this->body($apaleoRequest);
        if ($body !== null) {
            try {
                $encodedBody = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (\JsonException $jsonException) {
                throw new \InvalidArgumentException('Failed to encode request body as JSON: '.$jsonException->getMessage(), $jsonException->getCode(), previous: $jsonException);
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

    /** Same as execute(), but through Symfony's native non-blocking client instead of PSR-18. */
    private function executeAsync(Request $apaleoRequest, SymfonyHttpClientInterface $client, AccessToken $token): SymfonyResponseInterface
    {
        $uri = $this->baseUri.$apaleoRequest->endpoint();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$token->value,
                'Accept' => $apaleoRequest->accept(),
                ...$this->headers($apaleoRequest),
            ],
        ];

        $query = $this->query($apaleoRequest);
        if ($query !== []) {
            $options['query'] = $query;
        }

        $body = $this->body($apaleoRequest);
        if ($body !== null) {
            $options['json'] = $body;
        }

        // Non-blocking: I/O and any transport error happen lazily, on first read of the response.
        return $client->request($apaleoRequest->method()->value, $uri, $options);
    }

    /**
     * Bools as "true"/"false" (ASP.NET's binder rejects PHP's 1/0), and no enum placeholder
     * for an unrecognized API value may leak into a request.
     *
     * @return array<string, mixed>
     */
    private function query(Request $request): array
    {
        $query = array_map(static fn (mixed $value): mixed => \is_bool($value) ? ($value ? 'true' : 'false') : $value, $request->query());
        $this->assertNoUnknownEnum($query);

        return $query;
    }

    /**
     * A request's own headers can't replace the ones the SDK owns, whatever their casing.
     *
     * @return array<string, string>
     */
    private function headers(Request $request): array
    {
        return array_filter(
            $request->headers(),
            static fn (string $name): bool => !\in_array(strtolower($name), ['authorization', 'accept', 'content-type'], true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /** @return null|array<array-key, mixed> */
    private function body(Request $request): ?array
    {
        $body = $request->body();
        if ($body !== null) {
            $this->assertNoUnknownEnum($body);
        }

        return $body;
    }

    /**
     * Enums carry an Unknown/UnmappedValue case for values the API added after this SDK was
     * written. It's a read-side placeholder only: Apaleo would reject it, and silently dropping
     * it would widen a filter to "everything", so it's refused before anything is sent.
     *
     * @param array<array-key, mixed> $values
     */
    private function assertNoUnknownEnum(array $values): void
    {
        array_walk_recursive($values, static function (mixed $value, int|string $key): void {
            if (\is_string($value) && preg_match('/(?:^|,)__(?:unknown|unmapped)__(?:,|$)/', $value) === 1) {
                throw new \InvalidArgumentException("Cannot send an Unknown enum value for \"{$key}\": it stands for a value this SDK doesn't recognize, not one Apaleo accepts.");
            }
        });
    }

    private function excerpt(string $rawBody): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', strip_tags($rawBody)));

        // /u keeps the cut on a UTF-8 character boundary without requiring ext-mbstring.
        return (string) preg_replace('/^(.{200}).+$/su', '$1…', $flat);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapError(int $status, array $data, string $retryAfter, string $rawBody): ApaleoClientException|ApaleoServerException
    {
        $detail = $data['detail'] ?? $data['title'] ?? null;
        $message = match (true) {
            \is_string($detail) => $detail,
            $data === [] && $rawBody !== '' => "Apaleo API error (HTTP {$status}): ".$this->excerpt($rawBody),
            default => "Apaleo API error (HTTP {$status})",
        };
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
                retryAfterSeconds: $this->parseRetryAfter($retryAfter),
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
     * RFC 7231: Retry-After is either delta-seconds ("120") or an HTTP-date
     * ("Wed, 21 Oct 2026 07:28:00 GMT") — both appear in the wild.
     */
    private function parseRetryAfter(string $retryAfter): ?int
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
