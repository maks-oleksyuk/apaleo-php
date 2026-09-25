<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Inventory\Property;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\CreateProperty;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyFilter;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyResource;
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
final class PropertyResourceTest extends TestCase
{
    use MockPipeline;

    private PropertyResource $properties;

    /** @var array<string, mixed> */
    private array $fullPropertyFixture;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->properties = new PropertyResource($pipeline);

        $this->fullPropertyFixture = [
            'id' => 'BER',
            'code' => 'BER',
            'propertyTemplateId' => null,
            'isTemplate' => false,
            'name' => ['en' => 'Berlin Hotel'],
            'description' => ['en' => 'A nice hotel'],
            'companyName' => 'Berlin Hotel GmbH',
            'managingDirectors' => 'Jane Doe',
            'commercialRegisterEntry' => 'HRB 1',
            'taxId' => 'DE123456789',
            'location' => [
                'addressLine1' => 'Main St 1',
                'postalCode' => '10115',
                'city' => 'Berlin',
                'countryCode' => 'DE',
            ],
            'bankAccount' => ['bank' => 'Deutsche Bank'],
            'paymentTerms' => ['en' => 'Due on arrival'],
            'timeZone' => 'Europe/Berlin',
            'currencyCode' => 'EUR',
            'status' => 'Live',
            'isArchived' => false,
            'created' => '2024-01-01T00:00:00Z',
        ];
    }

    public function testGetPropertyMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullPropertyFixture)));

        $property = $this->properties->get('BER');

        self::assertSame('BER', $property->id);
        self::assertSame('Europe/Berlin', $property->timeZone);
        self::assertSame(PropertyStatus::Live, $property->status);
        self::assertSame('Berlin Hotel', $property->name['en']);
        self::assertSame('Main St 1', $property->location->addressLine1);
        self::assertSame('Deutsche Bank', $property->bankAccount?->bank);
    }

    public function testPlainStringNameIsNormalizedToLocalizedMap(): void
    {
        $fixture = $this->fullPropertyFixture;
        $fixture['name'] = 'Berlin Hotel';
        $fixture['description'] = 'A nice hotel';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $property = $this->properties->get('BER');

        self::assertSame('Berlin Hotel', $property->name['default']);
    }

    public function testMissingBankAccountIsNull(): void
    {
        $fixture = $this->fullPropertyFixture;
        unset($fixture['bankAccount']);
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $property = $this->properties->get('BER');

        self::assertNull($property->bankAccount);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullPropertyFixture;
        $fixture['status'] = 'SomeFutureStatus';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $property = $this->properties->get('BER');

        self::assertSame(PropertyStatus::Unknown, $property->status);
        self::assertSame('SomeFutureStatus', $property->rawStatus);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found',
            'title' => 'Property not found',
            'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->properties->get('MISSING');
    }

    public function testListPropertiesMapsWrappedResponse(): void
    {
        // list items use a flat shape: name/description are plain strings, not localized maps
        $first = $this->fullPropertyFixture;
        $first['name'] = 'Berlin Hotel';
        $first['description'] = 'A nice hotel';

        $second = $first;
        $second['id'] = 'VIE';
        $second['code'] = 'VIE';

        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 2,
            'properties' => [$first, $second],
        ])));

        $properties = $this->properties->list();

        self::assertCount(2, $properties);
        self::assertSame('BER', $properties[0]->id);
        self::assertSame('Berlin Hotel', $properties[0]->name);
        self::assertSame('VIE', $properties[1]->id);
    }

    public function testListPropertiesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->properties->list();
        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 42])));

        self::assertSame(42, $this->properties->count());
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'BER'])));

        $id = $this->properties->create(new CreateProperty(
            code: 'BER',
            name: ['en' => 'Berlin Hotel'],
            companyName: 'Berlin Hotel GmbH',
            commercialRegisterEntry: 'HRB 1',
            taxId: 'DE123456789',
            location: new Address('Main St 1', null, '10115', 'Berlin', null, 'DE'),
            paymentTerms: ['en' => 'Due on arrival'],
            timeZone: 'Europe/Berlin',
            defaultCheckInTime: '14:00:00',
            defaultCheckOutTime: '11:00:00',
            currencyCode: 'EUR',
        ));

        self::assertSame('BER', $id);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->update('BER', new JsonPatch()->replace('/name/en', 'New Name'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/name/en', 'value' => 'New Name']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->delete('BER');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testCloneReturnsClonedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'BER2'])));

        $id = $this->properties->clone('BER', new CreateProperty(
            code: 'BER2',
            name: ['en' => 'Berlin Hotel 2'],
            companyName: 'Berlin Hotel GmbH',
            commercialRegisterEntry: 'HRB 1',
            taxId: 'DE123456789',
            location: new Address('Main St 2', null, '10115', 'Berlin', null, 'DE'),
            paymentTerms: ['en' => 'Due on arrival'],
            timeZone: 'Europe/Berlin',
            defaultCheckInTime: '14:00:00',
            defaultCheckOutTime: '11:00:00',
            currencyCode: 'EUR',
        ));

        self::assertSame('BER2', $id);
    }

    public function testArchiveSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->archive('BER');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/property-actions/BER/archive', (string) $request->getUri());
    }

    public function testSetLiveSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->setLive('BER');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/property-actions/BER/set-live', (string) $request->getUri());
    }

    public function testResetSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->properties->reset('BER');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/property-actions/BER/reset', (string) $request->getUri());
    }

    public function testUnknownStatusInListFilterIsRejectedWithoutSendingARequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->properties->list(new PropertyFilter(status: [PropertyStatus::Unknown, PropertyStatus::Live]));
        } finally {
            self::assertFalse($this->httpClient->getLastRequest());
        }
    }
}
