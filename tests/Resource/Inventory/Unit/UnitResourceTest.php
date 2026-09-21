<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\Unit;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\CreateUnit;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\UnitResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class UnitResourceTest extends TestCase
{
    private MockClient $httpClient;

    private UnitResource $units;

    /** @var array<string, mixed> */
    private array $fullUnitFixture;

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
        $this->units = new UnitResource($pipeline);

        $this->fullUnitFixture = [
            'id' => 'U1',
            'name' => '101',
            'description' => 'Double Room',
            'property' => ['id' => 'BER'],
            'unitGroup' => ['id' => 'DBL'],
            'connectingUnit' => ['id' => 'U2'],
            'maxPersons' => 2,
            'status' => [
                'condition' => 'Clean',
                'isOccupied' => true,
                'maintenance' => [
                    'id' => 'M1',
                    'from' => '2024-01-01T00:00:00Z',
                    'to' => '2024-01-02T00:00:00Z',
                    'type' => 'OutOfOrder',
                    'description' => 'Broken heater',
                ],
            ],
            'isArchived' => false,
            'archived' => null,
            'attributes' => [
                ['id' => 'A1', 'name' => 'Balcony', 'description' => null],
            ],
            'connectedUnits' => [
                ['id' => 'U3', 'name' => '102', 'description' => 'Single Room', 'unitGroupId' => 'SGL', 'condition' => 'Clean', 'maxPersons' => 1],
            ],
            'created' => '2024-01-01T00:00:00Z',
        ];
    }

    public function testGetUnitMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullUnitFixture)));

        $unit = $this->units->get('U1');

        self::assertSame('U1', $unit->id);
        self::assertSame('BER', $unit->propertyId);
        self::assertSame('DBL', $unit->unitGroupId);
        self::assertSame('U2', $unit->connectingUnitId);
        self::assertSame(2, $unit->maxPersons);
        self::assertSame(UnitCondition::Clean, $unit->condition);
        self::assertTrue($unit->isOccupied);
        self::assertNotNull($unit->maintenance);
        self::assertSame(UnitMaintenanceType::OutOfOrder, $unit->maintenance->type);
        self::assertCount(1, $unit->attributes);
        self::assertSame('Balcony', $unit->attributes[0]->name);
        self::assertCount(1, $unit->connectedUnits);
        self::assertSame('U3', $unit->connectedUnits[0]->id);
    }

    public function testUnknownConditionFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullUnitFixture;
        $fixture['status'] = ['condition' => 'SomeFutureCondition', 'isOccupied' => true];
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $unit = $this->units->get('U1');

        self::assertSame(UnitCondition::Unknown, $unit->condition);
        self::assertSame('SomeFutureCondition', $unit->rawCondition);
        self::assertNull($unit->maintenance);
    }

    public function testListUnitsMapsWrappedResponse(): void
    {
        $second = $this->fullUnitFixture;
        $second['id'] = 'U2';

        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 2,
            'units' => [$this->fullUnitFixture, $second],
        ])));

        $units = $this->units->list('BER');

        self::assertCount(2, $units);
        self::assertSame('U1', $units[0]->id);
        self::assertSame('U2', $units[1]->id);
    }

    public function testListUnitsHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->units->list();
        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 62])));

        self::assertSame(62, $this->units->count(propertyId: '03_BS'));
    }

    public function testLocalizedDescriptionIsNormalized(): void
    {
        $fixture = $this->fullUnitFixture;
        $fixture['description'] = ['en' => 'Double Room', 'de' => 'Doppelzimmer'];
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $unit = $this->units->get('U1');

        self::assertSame('Doppelzimmer', $unit->description['de']);
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'U1'])));

        $id = $this->units->create(new CreateUnit('BER', '101', ['en' => 'Double Room'], 2, 'DBL'));

        self::assertSame('U1', $id);
    }

    public function testBulkCreateReturnsCreatedIds(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['ids' => ['U1', 'U2']])));

        $ids = $this->units->bulkCreate([
            new CreateUnit('BER', '101', ['en' => 'Double Room'], 2, 'DBL'),
            new CreateUnit('BER', '102', ['en' => 'Double Room'], 2, 'DBL'),
        ]);

        self::assertSame(['U1', 'U2'], $ids);
    }

    public function testBulkCreateThrowsOnMalformedResponseInsteadOfSilentlyReturningEmpty(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $this->expectException(ApaleoUnexpectedResponseException::class);

        $this->units->bulkCreate([new CreateUnit('BER', '101', ['en' => 'Double Room'], 2, 'DBL')]);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->units->update('U1', new JsonPatch()->replace('/name', 'New Name'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/name', 'value' => 'New Name']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testBulkUpdateSendsPatchWithUnitIdsQuery(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->units->bulkUpdate(['U1', 'U2'], new JsonPatch()->replace('/name', 'New Name'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertStringContainsString('unitIds=U1%2CU2', (string) $request->getUri());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->units->delete('U1');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testArchiveSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->units->archive('U1');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/unit-actions/U1/archive', (string) $request->getUri());
    }

    public function testUnknownConditionAndMaintenanceTypeAreOmittedFromQuery(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"count":0,"units":[]}'));

        $this->units->list(maintenanceType: UnitMaintenanceType::Unknown, condition: UnitCondition::Unknown);

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringNotContainsString('condition=', $uri);
        self::assertStringNotContainsString('maintenanceType=', $uri);
    }

    public function testListRejectsPageSizeAboveApaleosLimitWithoutSendingARequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->units->list(pageSize: 501);
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
