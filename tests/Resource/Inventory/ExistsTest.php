<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\InventoryResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExistsTest extends TestCase
{
    #[DataProvider('provideExistsSendsHeadAndMaps200And404Cases')]
    /** @param \Closure(InventoryResource): bool $exists */
    public function testExistsSendsHeadAndMaps200And404(\Closure $exists, string $path): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(200));
        $httpClient->addResponse(new Response(404));

        $factory = new Psr17Factory();
        $tokenProvider = new class implements TokenProvider {
            public function getToken(bool $forceRefresh = false): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };
        $inventory = new InventoryResource(new RequestPipeline($httpClient, $factory, $factory, $tokenProvider));

        self::assertTrue($exists($inventory));

        $request = $httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('HEAD', $request->getMethod());
        self::assertSame($path, $request->getUri()->getPath());

        self::assertFalse($exists($inventory));
    }

    /** @return iterable<string, array{\Closure(InventoryResource): bool, string}> */
    public static function provideExistsSendsHeadAndMaps200And404Cases(): iterable
    {
        yield 'property' => [static fn (InventoryResource $i): bool => $i->properties()->exists('MUC'), '/inventory/v1/properties/MUC'];

        yield 'unit' => [static fn (InventoryResource $i): bool => $i->units()->exists('MUC'), '/inventory/v1/units/MUC'];

        yield 'unit group' => [static fn (InventoryResource $i): bool => $i->unitGroups()->exists('MUC'), '/inventory/v1/unit-groups/MUC'];

        yield 'unit attribute' => [static fn (InventoryResource $i): bool => $i->unitAttributes()->exists('MUC'), '/inventory/v1/unit-attributes/MUC'];
    }
}
