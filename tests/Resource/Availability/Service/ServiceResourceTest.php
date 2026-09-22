<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Availability\Service;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Service\ServiceResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ServiceResourceTest extends TestCase
{
    private MockClient $httpClient;

    private ServiceResource $services;

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

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
