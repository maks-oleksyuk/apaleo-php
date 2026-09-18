<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\UnitAttribute;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO\CreateUnitAttributeDefinition;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\UnitAttributeResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class UnitAttributeResourceTest extends TestCase
{
    private MockClient $httpClient;

    private UnitAttributeResource $unitAttributes;

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
        $this->unitAttributes = new UnitAttributeResource($pipeline);
    }

    public function testGetUnitAttributeMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'id' => 'A1',
            'name' => 'Balcony',
            'description' => 'Room has a balcony',
        ])));

        $attribute = $this->unitAttributes->get('A1');

        self::assertSame('A1', $attribute->id);
        self::assertSame('Balcony', $attribute->name);
        self::assertSame('Room has a balcony', $attribute->description);
    }

    public function testListUnitAttributesMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 2,
            'unitAttributes' => [
                ['id' => 'A1', 'name' => 'Balcony', 'description' => null],
                ['id' => 'A2', 'name' => 'Sea View', 'description' => null],
            ],
        ])));

        $attributes = $this->unitAttributes->list();

        self::assertCount(2, $attributes);
        self::assertSame('A1', $attributes[0]->id);
        self::assertSame('A2', $attributes[1]->id);
        self::assertNull($attributes[0]->description);
    }

    public function testListUnitAttributesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        self::assertSame([], $this->unitAttributes->list());
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'A1'])));

        $id = $this->unitAttributes->create(new CreateUnitAttributeDefinition('Balcony', 'Room has a balcony'));

        self::assertSame('A1', $id);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->unitAttributes->update('A1', new JsonPatch()->replace('/name', 'New Name'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/name', 'value' => 'New Name']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->unitAttributes->delete('A1');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
