<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\UnitGroup;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\CreateUnitGroup;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\ReplaceUnitGroup;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\UnitGroupFilter;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\UnitGroupResource;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Inventory')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class UnitGroupResourceTest extends TestCase
{
    use MockPipeline;

    private UnitGroupResource $unitGroups;

    /** @var array<string, mixed> */
    private array $fixture;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->unitGroups = new UnitGroupResource($pipeline);

        $this->fixture = [
            'id' => 'DBL',
            'code' => 'DBL',
            'property' => ['id' => 'BER'],
            'name' => ['en' => 'Double Room'],
            'description' => ['en' => 'A double room'],
            'memberCount' => 10,
            'maxPersons' => 2,
            'rank' => 1,
            'type' => 'BedRoom',
            'connectedUnitGroups' => [
                ['id' => 'SGL', 'name' => 'Single Room', 'description' => 'A single room', 'memberCount' => 5, 'maxPersons' => 1],
            ],
        ];
    }

    public function testGetUnitGroupMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fixture)));

        $unitGroup = $this->unitGroups->get('DBL');

        self::assertSame('DBL', $unitGroup->id);
        self::assertSame('BER', $unitGroup->propertyId);
        self::assertSame('Double Room', $unitGroup->name['en']);
        self::assertSame(UnitGroupType::BedRoom, $unitGroup->type);
        self::assertCount(1, $unitGroup->connectedUnitGroups);
    }

    public function testUnknownTypeFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fixture;
        $fixture['type'] = 'SomeFutureType';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $unitGroup = $this->unitGroups->get('DBL');

        self::assertSame(UnitGroupType::Unknown, $unitGroup->type);
        self::assertSame('SomeFutureType', $unitGroup->rawType);
    }

    public function testListUnitGroupsMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'unitGroups' => [['name' => 'Double Room', 'description' => 'A double room'] + $this->fixture],
        ])));

        $unitGroups = $this->unitGroups->list(new UnitGroupFilter('BER'));

        self::assertCount(1, $unitGroups);
        self::assertSame('DBL', $unitGroups[0]->id);
        self::assertSame('Double Room', $unitGroups[0]->name);
    }

    public function testListUnitGroupsHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->unitGroups->list();
        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 7])));

        self::assertSame(7, $this->unitGroups->count(new UnitGroupFilter('BER')));
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'DBL'])));

        $id = $this->unitGroups->create(new CreateUnitGroup(
            code: 'DBL',
            propertyId: 'BER',
            name: ['en' => 'Double Room'],
            description: ['en' => 'A double room'],
            maxPersons: 2,
        ));

        self::assertSame('DBL', $id);
    }

    public function testReplaceSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->unitGroups->replace('DBL', new ReplaceUnitGroup(
            name: ['en' => 'Double Room Updated'],
            description: ['en' => 'Updated'],
            maxPersons: 3,
        ));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/unit-groups/DBL', (string) $request->getUri());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->unitGroups->delete('DBL');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testUnknownTypeInListFilterIsRejectedWithoutSendingARequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->unitGroups->list(new UnitGroupFilter(unitGroupTypes: [UnitGroupType::BedRoom, UnitGroupType::Unknown]));
        } finally {
            self::assertFalse($this->httpClient->getLastRequest());
        }
    }
}
