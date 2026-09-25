<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Types;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Booking\Types\Enum\AllowedValueType;
use Oleksyuk\Apaleo\Resource\Booking\Types\TypesResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Booking')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class TypesResourceTest extends TestCase
{
    use MockPipeline;

    private TypesResource $types;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
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
