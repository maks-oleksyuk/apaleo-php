<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo;

use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
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
        string $baseUri = 'https://api.apaleo.com',
    ) {
        $this->pipeline = new RequestPipeline($httpClient, $requestFactory, $streamFactory, $tokenProvider, $baseUri);
    }

    public function inventory(): InventoryResource
    {
        return new InventoryResource($this->pipeline);
    }
}
