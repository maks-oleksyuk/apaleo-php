<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\Types\Country;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\CountryResource;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CountryResourceTest extends TestCase
{
    private MockClient $httpClient;

    private CountryResource $countries;

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
        $this->countries = new CountryResource($pipeline);
    }

    public function testListReturnsCountryCodes(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'countryCodes' => ['DE', 'AT', 'CH'],
        ])));

        self::assertSame(['DE', 'AT', 'CH'], $this->countries->list());
    }

    public function testListHandlesEmptyResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        self::assertSame([], $this->countries->list());
    }
}
