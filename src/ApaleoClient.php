<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;
use Oleksyuk\Apaleo\Auth\InMemoryTokenCache;
use Oleksyuk\Apaleo\Auth\TokenCache;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\BookingResource;
use Oleksyuk\Apaleo\Resource\Inventory\InventoryResource;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ApaleoClient
{
    private RequestPipeline $pipeline;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        TokenProvider $tokenProvider,
        string $baseUri = RequestPipeline::DEFAULT_BASE_URI,
    ) {
        $this->pipeline = new RequestPipeline($httpClient, $requestFactory, $streamFactory, $tokenProvider, $baseUri);
    }

    public function inventory(): InventoryResource
    {
        return new InventoryResource($this->pipeline);
    }

    public function booking(): BookingResource
    {
        return new BookingResource($this->pipeline);
    }

    /**
     * Convenience constructor for the common case: auto-discovers a PSR-18 client and PSR-17
     * factories (via php-http/discovery) instead of requiring the caller to wire them up.
     * Requires an actual PSR-18 client implementation (e.g. symfony/http-client,
     * guzzlehttp/guzzle) to be installed — discovery can't invent one.
     */
    public static function create(
        string $clientId,
        #[\SensitiveParameter]
        string $clientSecret,
        TokenCache $tokenCache = new InMemoryTokenCache(),
        string $baseUri = RequestPipeline::DEFAULT_BASE_URI,
    ): self {
        $httpClient = Psr18ClientDiscovery::find();
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        $tokenProvider = new ClientCredentialsTokenProvider(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $clientId,
            $clientSecret,
            $tokenCache,
        );

        return new self($httpClient, $requestFactory, $streamFactory, $tokenProvider, $baseUri);
    }
}
