<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Types;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Types\Enum\AllowedValueType;
use Oleksyuk\Apaleo\Resource\Booking\Types\TypesResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class TypesResourceTest extends TestCase
{
    private MockClient $httpClient;

    private TypesResource $types;

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
        $this->types = new TypesResource($pipeline);
    }

    public function testSourcesReturnsList(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'sources' => ['Booking.com', 'Expedia'],
        ])));

        self::assertSame(['Booking.com', 'Expedia'], $this->types->sources());
    }

    public function testSourcesHandlesEmptyResponse(): void
    {
        $this->httpClient->addResponse(new Response(204));

        self::assertSame([], $this->types->sources());
    }

    public function testAllowedValuesReturnsList(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'allowedValues' => ['PassportNumber', 'DriverLicenseNumber'],
            'count' => 2,
        ])));

        $values = $this->types->allowedValues(AllowedValueType::IdentificationType, 'DE');

        self::assertSame(['PassportNumber', 'DriverLicenseNumber'], $values);

        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertStringContainsString('/booking/v1/types/IdentificationType/allowed-values', (string) $request->getUri());
        self::assertStringContainsString('countryCode=DE', (string) $request->getUri());
    }

    public function testAllowedValuesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        self::assertSame([], $this->types->allowedValues(AllowedValueType::Gender, 'DE'));
    }
}
