<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\Types\Country;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\CountryResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Inventory')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class CountryResourceTest extends TestCase
{
    use MockPipeline;

    private CountryResource $countries;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
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
