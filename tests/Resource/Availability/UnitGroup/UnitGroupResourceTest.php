<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Availability\UnitGroup;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\UnitGroupResource;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Availability')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class UnitGroupResourceTest extends TestCase
{
    use MockPipeline;

    private UnitGroupResource $unitGroups;

    /** @var array<string, mixed> */
    private array $fullTimeSliceFixture;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->unitGroups = new UnitGroupResource($pipeline);

        $this->fullTimeSliceFixture = [
            'from' => '2026-09-20T17:00:00+02:00',
            'to' => '2026-09-21T11:00:00+02:00',
            'property' => [
                'physicalCount' => 20, 'houseCount' => 20, 'soldCount' => 10, 'occupancy' => 50.0,
                'sellableCount' => 10, 'allowedOverbookingCount' => 0,
                'maintenance' => ['outOfService' => 0, 'outOfOrder' => 0, 'outOfInventory' => 0],
                'block' => ['definite' => 0, 'tentative' => 0, 'optional' => 0, 'optionalDeducting' => 0, 'picked' => 0, 'remaining' => 0],
            ],
            'unitGroups' => [[
                'unitGroup' => ['id' => 'MUC-SGL', 'type' => 'BedRoom'],
                'physicalCount' => 5, 'houseCount' => 5, 'soldCount' => 2, 'occupancy' => 40.0,
                'availableCount' => 3, 'sellableCount' => 3, 'allowedOverbookingCount' => 0,
                'maintenance' => ['outOfService' => 0, 'outOfOrder' => 0, 'outOfInventory' => 0],
                'block' => ['definite' => 0, 'tentative' => 0, 'optional' => 0, 'optionalDeducting' => 0, 'picked' => 0, 'remaining' => 0],
            ]],
        ];
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'timeSlices' => [$this->fullTimeSliceFixture],
        ])));

        $result = $this->unitGroups->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), pageSize: 10);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);
        self::assertSame(10, $result->items[0]->property->soldCount);
        self::assertSame('MUC-SGL', $result->items[0]->unitGroups[0]->unitGroup->id);
        self::assertSame(3, $result->items[0]->unitGroups[0]->availableCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
        self::assertStringContainsString('pageSize=10', (string) $request->getUri());
    }

    public function testListHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->unitGroups->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'));

        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->unitGroups->update(
            'MUC-SGL',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-25'),
            TimeSliceTemplate::OverNight,
            new JsonPatch()->replace('/allowedOverbookingCount', 2),
        );

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertStringContainsString('/availability/v1/unit-groups/MUC-SGL', (string) $request->getUri());
        self::assertSame(
            [['op' => 'replace', 'path' => '/allowedOverbookingCount', 'value' => 2]],
            json_decode((string) $request->getBody(), true),
        );
    }
}
