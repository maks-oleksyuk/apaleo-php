<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Block;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Block\BlockFilter;
use Oleksyuk\Apaleo\Resource\Booking\Block\BlockResource;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\CreateBlock;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\CreateBlockTimeSlice;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\ReplaceBlock;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\OptionalCutoffBehavior;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class BlockResourceTest extends TestCase
{
    private MockClient $httpClient;

    private BlockResource $blocks;

    /** @var array<string, mixed> */
    private array $fullBlockFixture;

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
        $this->blocks = new BlockResource($pipeline);

        $this->fullBlockFixture = [
            'id' => 'MUC-HSGTDG',
            'group' => ['id' => 'XPGMSXGF', 'name' => 'apaleo Summer Festival 2027'],
            'status' => 'Tentative',
            'property' => ['id' => 'MUC'],
            'ratePlan' => ['id' => 'MUC-NONREF_SGL', 'isSubjectToCityTax' => false],
            'unitGroup' => ['id' => 'MUC-SGL'],
            'grossDailyRate' => ['amount' => 160.0, 'currency' => 'EUR'],
            'from' => '2026-09-23T17:00:00+02:00',
            'to' => '2026-09-25T11:00:00+02:00',
            'pickedReservations' => 0,
            'created' => '2026-09-18T11:31:09+02:00',
            'modified' => '2026-09-18T11:31:09+02:00',
        ];
    }

    public function testGetBlockMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullBlockFixture)));

        $block = $this->blocks->get('MUC-HSGTDG');

        self::assertSame('MUC-HSGTDG', $block->id);
        self::assertSame(BlockStatus::Tentative, $block->status);
        self::assertSame('XPGMSXGF', $block->group->id);
        self::assertSame(160.0, $block->grossDailyRate->amount);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullBlockFixture;
        $fixture['status'] = 'SomeFutureStatus';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        self::assertSame(BlockStatus::Unknown, $this->blocks->get('MUC-HSGTDG')->status);
    }

    public function testExistsReturnsTrueOn200(): void
    {
        $this->httpClient->addResponse(new Response(200));

        self::assertTrue($this->blocks->exists('MUC-HSGTDG'));

        self::assertSame('HEAD', $this->lastRequest()->getMethod());
    }

    public function testExistsReturnsFalseOn404(): void
    {
        $this->httpClient->addResponse(new Response(404));

        self::assertFalse($this->blocks->exists('MISSING'));
    }

    public function testGetNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found', 'title' => 'Block not found', 'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->blocks->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'blocks' => [$this->fullBlockFixture],
        ])));

        $result = $this->blocks->list(new BlockFilter(groupId: 'XPGMSXGF'), pageSize: 10);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('groupId=XPGMSXGF', (string) $request->getUri());
        self::assertStringContainsString('pageSize=10', (string) $request->getUri());
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 3])));

        self::assertSame(3, $this->blocks->count(new BlockFilter(status: [BlockStatus::Definite])));

        self::assertStringContainsString('status=Definite', (string) $this->lastRequest()->getUri());
    }

    public function testCreateReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'MUC-HSGTDG'])));

        $id = $this->blocks->create(new CreateBlock(
            groupId: 'XPGMSXGF',
            ratePlanId: 'MUC-NONREF_SGL',
            from: '2026-09-23',
            to: '2026-09-26',
            grossDailyRate: new MonetaryValue(160.0, 'EUR'),
            timeSlices: [new CreateBlockTimeSlice(3)],
        ));

        self::assertSame('MUC-HSGTDG', $id);

        /** @var array{timeSlices: list<array{blockedUnits: int}>} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame(3, $body['timeSlices'][0]['blockedUnits']);
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->update('MUC-HSGTDG', new JsonPatch()->replace('/promoCode', 'SUMMER'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/promoCode', 'value' => 'SUMMER']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->delete('MUC-HSGTDG');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testAmendSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->amend('MUC-HSGTDG', new ReplaceBlock(
            from: '2026-09-23',
            to: '2026-09-27',
            grossDailyRate: new MonetaryValue(170.0, 'EUR'),
            timeSlices: [new CreateBlockTimeSlice(4)],
        ));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/block-actions/MUC-HSGTDG/amend', (string) $request->getUri());

        /** @var array{grossDailyRate: array{amount: float}} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame(170.0, (float) $body['grossDailyRate']['amount']);
    }

    #[DataProvider('provideSimpleActionSendsPutRequestCases')]
    public function testSimpleActionSendsPutRequest(string $method, string $expectedPathSegment): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->{$method}('MUC-HSGTDG');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/block-actions/MUC-HSGTDG/'.$expectedPathSegment, (string) $request->getUri());
    }

    /** @return iterable<string, list<string>> */
    public static function provideSimpleActionSendsPutRequestCases(): iterable
    {
        yield 'cancel' => ['cancel', 'cancel'];

        yield 'confirm' => ['confirm', 'confirm'];

        yield 'release' => ['release', 'release'];

        yield 'wash' => ['wash', 'wash'];
    }

    public function testSetToOptionalSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->setToOptional('MUC-HSGTDG', '2026-09-22', true, OptionalCutoffBehavior::AutoRelease);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame(
            ['optionalCutoff' => '2026-09-22', 'isOptionalDeductingInventory' => true, 'optionalCutoffBehavior' => 'AutoRelease'],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testCutoffOptionalSendsExpectedTimestamp(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->cutoffOptional('MUC-HSGTDG', new \DateTimeImmutable('2026-09-22T00:00:00+02:00'));

        self::assertStringContainsString('/cutoff-optional', (string) $this->lastRequest()->getUri());
    }

    public function testStartOptionalSendsExpectedTimestamp(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->blocks->startOptional('MUC-HSGTDG', new \DateTimeImmutable('2026-09-22T00:00:00+02:00'));

        self::assertStringContainsString('/start-optional', (string) $this->lastRequest()->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
