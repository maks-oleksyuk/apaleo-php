<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;
use Oleksyuk\Apaleo\Auth\InMemoryTokenCache;
use Oleksyuk\Apaleo\Auth\TokenCache;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\AvailabilityResource;
use Oleksyuk\Apaleo\Resource\Booking\BookingResource;
use Oleksyuk\Apaleo\Resource\Inventory\InventoryResource;
use Oleksyuk\Apaleo\Resource\Logs\LogsResource;
use Oleksyuk\Apaleo\Resource\Operations\OperationsResource;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlanResource;
use Oleksyuk\Apaleo\Resource\Reports\ReportsResource;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;

final readonly class ApaleoClient
{
    private RequestPipeline $pipeline;

    /** $asyncHttpClient enables sendMany()'s concurrent dispatch; apaleo-bundle wires it automatically. */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        TokenProvider $tokenProvider,
        string $baseUri = RequestPipeline::DEFAULT_BASE_URI,
        ?SymfonyHttpClientInterface $asyncHttpClient = null,
    ) {
        $this->pipeline = new RequestPipeline($httpClient, $requestFactory, $streamFactory, $tokenProvider, $baseUri, $asyncHttpClient);
    }

    public function inventory(): InventoryResource
    {
        return new InventoryResource($this->pipeline);
    }

    public function booking(): BookingResource
    {
        return new BookingResource($this->pipeline);
    }

    public function availability(): AvailabilityResource
    {
        return new AvailabilityResource($this->pipeline);
    }

    public function reports(): ReportsResource
    {
        return new ReportsResource($this->pipeline);
    }

    public function logs(): LogsResource
    {
        return new LogsResource($this->pipeline);
    }

    public function ratePlan(): RatePlanResource
    {
        return new RatePlanResource($this->pipeline);
    }

    public function operations(): OperationsResource
    {
        return new OperationsResource($this->pipeline);
    }

    /**
     * Escape hatch: sends one low-level Request, either an SDK one (`new ListReservationsRequest(...)`)
     * or your own subclass for an endpoint the SDK doesn't cover yet.
     *
     * @return array<string, mixed> decoded response body; map it with the matching DTO's fromArray()
     */
    public function send(Request $request): array
    {
        return $this->pipeline->send($request);
    }

    /**
     * Batch escape hatch: sends several low-level Request objects concurrently, e.g.
     * `new ListReservationsRequest(...)`. Decode each result with the matching DTO's fromArray().
     *
     * All-or-nothing: if any request fails, its exception is thrown and the other results are lost.
     *
     * @param list<Request> $requests
     *
     * @return list<array<string, mixed>> decoded response bodies, in the same order as $requests
     */
    public function sendMany(array $requests): array
    {
        return $this->pipeline->sendMany($requests);
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

        // $asyncHttpClient isn't auto-discovered: it would silently run through a second,
        // differently-configured client. Pass it to the main constructor explicitly if you want it.
        return new self($httpClient, $requestFactory, $streamFactory, $tokenProvider, $baseUri);
    }
}
