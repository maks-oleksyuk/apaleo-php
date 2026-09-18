<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\Property;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyResource;
use PHPUnit\Framework\TestCase;

final class PropertyResourceTest extends TestCase
{
    private MockClient $httpClient;
    private PropertyResource $properties;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $tokenProvider = new class implements TokenProvider {
            public function getToken(): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };

        $pipeline = new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider);
        $this->properties = new PropertyResource($pipeline);
    }

    public function testGetPropertyMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'id' => 'BER',
            'code' => 'BER',
            'name' => ['en' => 'Berlin Hotel'],
            'timeZone' => 'Europe/Berlin',
            'currencyCode' => 'EUR',
            'status' => 'Live',
            'isArchived' => false,
            'created' => '2024-01-01T00:00:00Z',
        ])));

        $property = $this->properties->get('BER');

        self::assertSame('BER', $property->id);
        self::assertSame('Europe/Berlin', $property->timeZone);
        self::assertSame(PropertyStatus::Live, $property->status);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'id' => 'BER',
            'code' => 'BER',
            'name' => [],
            'timeZone' => 'Europe/Berlin',
            'currencyCode' => 'EUR',
            'status' => 'SomeFutureStatus',
            'isArchived' => false,
            'created' => '2024-01-01T00:00:00Z',
        ])));

        $property = $this->properties->get('BER');

        self::assertSame(PropertyStatus::Unknown, $property->status);
        self::assertSame('SomeFutureStatus', $property->rawStatus);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found',
            'title' => 'Property not found',
            'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->properties->get('MISSING');
    }

    public function testListPropertiesMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 2,
            'properties' => [
                ['id' => 'BER', 'code' => 'BER', 'name' => [], 'timeZone' => 'Europe/Berlin', 'currencyCode' => 'EUR', 'status' => 'Live', 'isArchived' => false, 'created' => '2024-01-01T00:00:00Z'],
                ['id' => 'VIE', 'code' => 'VIE', 'name' => [], 'timeZone' => 'Europe/Vienna', 'currencyCode' => 'EUR', 'status' => 'Test', 'isArchived' => false, 'created' => '2024-01-01T00:00:00Z'],
            ],
        ])));

        $properties = $this->properties->list();

        self::assertCount(2, $properties);
        self::assertSame('BER', $properties[0]->id);
        self::assertSame('VIE', $properties[1]->id);
    }

    public function testListPropertiesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        self::assertSame([], $this->properties->list());
    }
}
