<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Availability\Service;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Availability\Service\ServiceResource;
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
final class ServiceResourceTest extends TestCase
{
    use MockPipeline;

    private ServiceResource $services;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->services = new ServiceResource($pipeline);
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'timeSlices' => [[
                'from' => '2026-09-20T00:00:00+02:00',
                'to' => '2026-09-21T00:00:00+02:00',
                'services' => [[
                    'service' => ['id' => 'MUC-BRKF', 'name' => 'Breakfast'],
                    'quantity' => 50,
                    'soldCount' => 20,
                    'availableCount' => 30,
                    'serviceDate' => '2026-09-20',
                    'block' => ['definite' => 0, 'tentative' => 0, 'optional' => 0, 'optionalDeducting' => 0, 'picked' => 0, 'remaining' => 0],
                ]],
            ]],
        ])));

        $result = $this->services->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'));

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);
        self::assertSame('MUC-BRKF', $result->items[0]->services[0]->service->id);
        self::assertSame(30, $result->items[0]->services[0]->availableCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
    }

    public function testListHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->services->list('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'));

        self::assertCount(0, $result);
    }
}
