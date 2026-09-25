<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Operations;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Operations\DTO\CreateMaintenance;
use Oleksyuk\Apaleo\Resource\Operations\DTO\UnitConditionUpdate;
use Oleksyuk\Apaleo\Resource\Operations\MaintenanceFilter;
use Oleksyuk\Apaleo\Resource\Operations\OperationsResource;
use Oleksyuk\Apaleo\Resource\Shared\Enum\MaintenanceType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class OperationsResourceTest extends TestCase
{
    private MockClient $httpClient;

    private OperationsResource $operations;

    /** @var array<string, mixed> */
    private array $maintenanceFixture;

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
        $this->operations = new OperationsResource($pipeline);

        $this->maintenanceFixture = [
            'id' => 'MUC-JQI-SGHZD',
            'unit' => ['id' => 'MUC-JQI', 'name' => 'A.102', 'description' => 'Double room', 'unitGroupId' => 'MUC-DBL'],
            'from' => '2026-09-23T15:19:48+02:00',
            'to' => '2026-09-25T15:19:48+02:00',
            'type' => 'OutOfService',
            'description' => 'The remote control for the TV needs to be replaced.',
        ];
    }

    public function testGetMaintenanceMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->maintenanceFixture)));

        $maintenance = $this->operations->getMaintenance('MUC-JQI-SGHZD');

        self::assertSame('MUC-JQI-SGHZD', $maintenance->id);
        self::assertSame('MUC-JQI', $maintenance->unit->id);
        self::assertSame(MaintenanceType::OutOfService, $maintenance->type);
        self::assertSame('2026-09-25', $maintenance->to->format('Y-m-d'));
    }

    public function testUnknownTypeFallsBackWithoutThrowing(): void
    {
        $fixture = $this->maintenanceFixture;
        $fixture['type'] = 'SomeFutureType';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $maintenance = $this->operations->getMaintenance('MUC-JQI-SGHZD');

        self::assertSame(MaintenanceType::Unknown, $maintenance->type);
    }

    public function testMaintenanceExistsReturnsTrueOn200(): void
    {
        $this->httpClient->addResponse(new Response(200));

        self::assertTrue($this->operations->maintenanceExists('MUC-JQI-SGHZD'));
    }

    public function testMaintenanceExistsReturnsFalseOn404(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found',
            'title' => 'Maintenance not found',
            'status' => 404,
        ])));

        self::assertFalse($this->operations->maintenanceExists('MISSING'));
    }

    public function testListMaintenancesMapsWrappedResponseAndSendsFilter(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'maintenances' => [$this->maintenanceFixture],
        ])));

        $result = $this->operations->listMaintenances(new MaintenanceFilter(propertyId: 'MUC', types: [MaintenanceType::OutOfService]), pageSize: 10);

        self::assertSame(1, $result->totalCount);
        self::assertSame('MUC-JQI-SGHZD', $result[0]->id);
        self::assertStringContainsString('propertyId=MUC', $this->lastUri());
        self::assertStringContainsString('types=OutOfService', $this->lastUri());
        self::assertStringContainsString('pageSize=10', $this->lastUri());
    }

    public function testListMaintenancesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->operations->listMaintenances();
        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testCountMaintenancesReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 7])));

        self::assertSame(7, $this->operations->countMaintenances(new MaintenanceFilter(unitId: 'MUC-JQI')));
        self::assertStringContainsString('unitId=MUC-JQI', $this->lastUri());
    }

    public function testCreateMaintenanceReturnsCreatedIdAndSendsIdempotencyKey(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'MUC-JQI-SGHZD'])));

        $id = $this->operations->createMaintenance(
            new CreateMaintenance(
                unitId: 'MUC-JQI',
                from: new \DateTimeImmutable('2026-09-23T15:19:48+02:00'),
                to: new \DateTimeImmutable('2026-09-25T15:19:48+02:00'),
                type: MaintenanceType::OutOfService,
                description: 'The remote control for the TV needs to be replaced.',
            ),
            idempotencyKey: 'retry-key-1',
        );

        self::assertSame('MUC-JQI-SGHZD', $id);
        self::assertSame('retry-key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testBulkCreateMaintenancesReturnsIds(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['ids' => ['MUC-JQI-SGHZD', 'MUC-JQI-GSZGK']])));

        $ids = $this->operations->bulkCreateMaintenances([
            new CreateMaintenance('MUC-JQI', new \DateTimeImmutable('2026-09-23'), new \DateTimeImmutable('2026-09-25'), MaintenanceType::OutOfService),
            new CreateMaintenance('MUC-MTA', new \DateTimeImmutable('2026-09-23'), new \DateTimeImmutable('2026-09-25'), MaintenanceType::OutOfService),
        ]);

        self::assertSame(['MUC-JQI-SGHZD', 'MUC-JQI-GSZGK'], $ids);
    }

    public function testUpdateMaintenanceSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->operations->updateMaintenance('MUC-JQI-SGHZD', new JsonPatch()->replace('/description', 'Fixed'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/description', 'value' => 'Fixed']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testDeleteMaintenanceSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->operations->deleteMaintenance('MUC-JQI-SGHZD');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testPerformNightAuditSendsQuery(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->operations->performNightAudit('MUC', setReservationsToNoShow: false);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
        self::assertStringContainsString('setReservationsToNoShow=false', (string) $request->getUri());
    }

    public function testSetUnitsConditionSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->operations->setUnitsCondition([
            new UnitConditionUpdate('UNI-EXA', UnitCondition::Clean),
            new UnitConditionUpdate('UNI-DBL', UnitCondition::Dirty),
        ]);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame(
            ['unitsConditions' => [
                ['id' => 'UNI-EXA', 'condition' => 'Clean'],
                ['id' => 'UNI-DBL', 'condition' => 'Dirty'],
            ]],
            json_decode((string) $request->getBody(), true),
        );
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }

    private function lastUri(): string
    {
        return (string) $this->lastRequest()->getUri();
    }
}
