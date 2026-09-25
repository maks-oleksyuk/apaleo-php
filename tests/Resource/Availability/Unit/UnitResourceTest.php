<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Availability\Unit;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Unit\UnitResource;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
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
            'id' => 'MUC-MTA',
            'name' => 'A.101',
            'description' => 'Single room',
            'property' => ['id' => 'MUC'],
            'unitGroup' => ['id' => 'MUC-SGL', 'type' => 'BedRoom'],
            'status' => ['isOccupied' => false, 'condition' => 'Clean'],
            'maxPersons' => 1,
            'attributes' => [['id' => 'ATTR1', 'name' => 'Balcony']],
        ];
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'units' => [$this->fullUnitFixture],
        ])));

        $result = $this->units->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), unitCondition: UnitCondition::Clean);

        self::assertCount(1, $result);
        self::assertSame('MUC-MTA', $result->items[0]->id);
        self::assertSame(UnitCondition::Clean, $result->items[0]->status->condition);
        self::assertSame('Balcony', $result->items[0]->attributes[0]->name);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
        self::assertStringContainsString('unitCondition=Clean', (string) $request->getUri());
    }

    public function testListHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->units->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'));

        self::assertCount(0, $result);
    }

    public function testForReservationMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'units' => [$this->fullUnitFixture],
        ])));

        $result = $this->units->forReservation('XPGMSXGF-1');

        self::assertCount(1, $result);
        self::assertSame('MUC-MTA', $result->items[0]->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('/availability/v1/reservations/XPGMSXGF-1/units', (string) $request->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
