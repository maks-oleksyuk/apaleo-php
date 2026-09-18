<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Support\ResponseData;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ClientCredentialsTokenProvider implements TokenProvider
{
    private const string CACHE_KEY_PREFIX = 'apaleo_token_';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $clientId,
        private string $clientSecret,
        private TokenCache $cache = new InMemoryTokenCache(),
        private string $identityBaseUri = 'https://identity.apaleo.com',
    ) {
    }

    public function getToken(): AccessToken
    {
        $cacheKey = self::CACHE_KEY_PREFIX.$this->clientId;

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof AccessToken && !$cached->isExpired()) {
            return $cached;
        }

        $token = $this->requestToken();
        $this->cache->set($cacheKey, $token);

        return $token;
    }

    private function requestToken(): AccessToken
    {
        $body = $this->streamFactory->createStream('grant_type=client_credentials');

        $request = $this->requestFactory
            ->createRequest('POST', $this->identityBaseUri.'/connect/token')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret))
            ->withBody($body)
        ;

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new ApaleoTransportException('Failed to reach Apaleo identity server: '.$clientException->getMessage(), $clientException->getCode(), previous: $clientException);
        }

        $decoded = json_decode((string) $response->getBody(), true);

        /** @var array<string, mixed> $data */
        $data = \is_array($decoded) ? $decoded : [];

        if ($response->getStatusCode() >= 400) {
            $errorType = \is_string($data['error'] ?? null) ? $data['error'] : null;
            $description = $data['error_description'] ?? null;
            $message = \is_string($description) ? $description : ('Failed to obtain Apaleo access token'.($errorType !== null ? ": {$errorType}" : ''));

            throw new ApaleoAuthException(
                message: $message,
                statusCode: $response->getStatusCode(),
                apaleoErrorType: $errorType,
                rawResponse: $data,
            );
        }

        return new AccessToken(
            value: ResponseData::string($data, 'access_token'),
            expiresAt: new \DateTimeImmutable('+'.ResponseData::int($data, 'expires_in').' seconds'),
        );
    }
}
