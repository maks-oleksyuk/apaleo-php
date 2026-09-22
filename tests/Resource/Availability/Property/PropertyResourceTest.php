<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Availability\Property;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Property\PropertyResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class PropertyResourceTest extends TestCase
{
    private MockClient $httpClient;

    private PropertyResource $properties;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $tokenProvider = new class implements TokenProvider {
            public function getToken(bool $forceRefresh = false): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };

        $pipeline = new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider);
        $this->properties = new PropertyResource($pipeline);
    }

    public function testHouseOverbookingMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'timeSlices' => [[
                'from' => '2026-09-20T17:00:00+02:00',
                'to' => '2026-09-21T11:00:00+02:00',
                'houseOverbookingLimit' => 5,
            ]],
        ])));

        $slices = $this->properties->houseOverbooking('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-25'));

        self::assertCount(1, $slices);
        self::assertSame(5, $slices[0]->houseOverbookingLimit);

        $request = $this->lastRequest();
        self::assertStringContainsString('/availability/v1/properties/MUC', (string) $request->getUri());
        self::assertStringContainsString('timeSliceTemplate=OverNight', (string) $request->getUri());
        self::assertStringContainsString('unitGroupType=BedRoom', (string) $request->getUri());
    }

    public function testHouseOverbookingHandlesEmptyResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"count":0,"timeSlices":[]}'));

        self::assertSame([], $this->properties->houseOverbooking('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-25')));
    }

    public function testUpdateHouseOverbookingSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->updateHouseOverbooking(
            'MUC',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-25'),
            new JsonPatch()->replace('/houseOverbookingLimit', 3),
        );

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/houseOverbookingLimit', 'value' => 3]],
            json_decode((string) $request->getBody(), true),
        );
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
