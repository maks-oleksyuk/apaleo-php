<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Group;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\CreateGroup;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\PickUpReservation;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupFilter;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupResource;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Booking')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class GroupResourceTest extends TestCase
{
    use MockPipeline;

    private GroupResource $groups;

    /** @var array<string, mixed> */
    private array $fullGroupFixture;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->groups = new GroupResource($pipeline);

        $this->fullGroupFixture = [
            'id' => 'XPGMSXGF',
            'name' => 'apaleo Summer Festival 2027',
            'booker' => ['lastName' => 'Doe'],
            'hasActivePaymentAccount' => false,
            'created' => '2026-09-18T11:31:09+02:00',
            'modified' => '2026-09-18T11:31:09+02:00',
            'propertyIds' => ['MUC'],
            'blocks' => [[
                'id' => 'MUC-HSGTDG',
                'status' => 'Tentative',
                'property' => ['id' => 'MUC'],
                'ratePlan' => ['id' => 'MUC-NONREF_SGL', 'isSubjectToCityTax' => false],
                'unitGroup' => ['id' => 'MUC-SGL'],
                'grossDailyRate' => ['amount' => 160.0, 'currency' => 'EUR'],
                'from' => '2026-09-23T17:00:00+02:00',
                'to' => '2026-09-25T11:00:00+02:00',
                'blockedUnits' => 10,
                'pickedReservations' => 0,
                'created' => '2026-09-18T11:31:09+02:00',
                'modified' => '2026-09-18T11:31:09+02:00',
            ]],
        ];
    }

    public function testGetGroupMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullGroupFixture)));

        $group = $this->groups->get('XPGMSXGF');

        self::assertSame('XPGMSXGF', $group->id);
        self::assertSame('Doe', $group->booker?->lastName);
        self::assertSame(['MUC'], $group->propertyIds);
        self::assertCount(1, $group->blocks);
        self::assertSame('MUC-HSGTDG', $group->blocks[0]->id);
    }

    public function testExistsReturnsTrueOn200(): void
    {
        $this->httpClient->addResponse(new Response(200));

        self::assertTrue($this->groups->exists('XPGMSXGF'));
        self::assertSame('HEAD', $this->lastRequest()->getMethod());
    }

    public function testExistsReturnsFalseOn404(): void
    {
        $this->httpClient->addResponse(new Response(404));

        self::assertFalse($this->groups->exists('MISSING'));
    }

    public function testGetNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found', 'title' => 'Group not found', 'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->groups->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'groups' => [$this->fullGroupFixture],
        ])));

        $result = $this->groups->list(new GroupFilter(textSearch: 'Summer'), pageSize: 10);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('textSearch=Summer', (string) $request->getUri());
        self::assertStringContainsString('pageSize=10', (string) $request->getUri());
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 5])));

        self::assertSame(5, $this->groups->count(new GroupFilter(propertyIds: ['MUC'])));

        self::assertStringContainsString('propertyIds=MUC', (string) $this->lastRequest()->getUri());
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'XPGMSXGF'])));

        $id = $this->groups->create(new CreateGroup(
            name: 'apaleo Summer Festival 2027',
            booker: new Booker('Doe'),
            propertyIds: ['MUC'],
        ));

        self::assertSame('XPGMSXGF', $id);

        /** @var array{propertyIds: list<string>} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame(['MUC'], $body['propertyIds']);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->groups->update('XPGMSXGF', new JsonPatch()->replace('/comment', 'VIP group'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/comment', 'value' => 'VIP group']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->groups->delete('XPGMSXGF');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testPickUpReservationsReturnsCreatedIds(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode([
            'reservationIds' => [['id' => 'XPGMSXGF-1'], ['id' => 'XPGMSXGF-2']],
        ])));

        $created = $this->groups->pickUpReservations('XPGMSXGF', [new PickUpReservation(
            blockId: 'MUC-HSGTDG',
            arrival: '2026-09-20',
            departure: '2026-09-22',
            adults: 1,
        )]);

        self::assertSame(['XPGMSXGF-1', 'XPGMSXGF-2'], $created->reservationIds);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringContainsString('/groups/XPGMSXGF/reservations', (string) $request->getUri());

        /** @var array{reservations: list<array{blockId: string}>} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('MUC-HSGTDG', $body['reservations'][0]['blockId']);
    }
}
