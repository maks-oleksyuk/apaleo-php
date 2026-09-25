<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Settings;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\DTO\CapturePolicy;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO\MarketSegment;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO\SubAccount;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO\TimeSliceDefinition;
use Oleksyuk\Apaleo\Tests\Support\Fixture;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * Get/update/delete/exists/count round-trips; responses are built from the DTO constructors by Fixture.
 *
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Settings')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class SettingsCrudTest extends SettingsTestCase
{
    public function testCapturePolicyGet(): void
    {
        $this->respond(Fixture::response(CapturePolicy::class));

        self::assertInstanceOf(CapturePolicy::class, $this->api->capturePolicies()->get('CAP1'));
    }

    public function testCityTaxUpdateAndDelete(): void
    {
        $this->respondEmpty();
        $this->api->cityTaxes()->update('CT1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->cityTaxes()->delete('CT1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testFeatureAndInvoiceAddressUpdate(): void
    {
        $this->respondEmpty();
        $this->api->features()->update('MUC', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->invoiceAddresses()->update(['MUC'], $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());
    }

    public function testMarketSegmentGetExistsCountUpdateDelete(): void
    {
        $this->respond(Fixture::response(MarketSegment::class));
        self::assertInstanceOf(MarketSegment::class, $this->api->marketSegments()->get('MS1'));

        $this->httpClient->addResponse(new Response(404));
        self::assertFalse($this->api->marketSegments()->exists('MS9'));

        $this->respond(['count' => 3]);
        self::assertSame(3, $this->api->marketSegments()->count(['MUC']));

        $this->respondEmpty();
        $this->api->marketSegments()->update('MS1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->marketSegments()->delete('MS1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testSubAccountGetExistsUpdateDelete(): void
    {
        $this->respond(Fixture::response(SubAccount::class));
        self::assertInstanceOf(SubAccount::class, $this->api->subAccounts()->get('SA1'));

        $this->httpClient->addResponse(new Response(200));
        $this->httpClient->addResponse(new Response(404));
        self::assertTrue($this->api->subAccounts()->exists('SA1'));
        self::assertFalse($this->api->subAccounts()->exists('SA9'));

        $this->respondEmpty();
        $this->api->subAccounts()->update('SA1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->subAccounts()->delete('SA1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testTimeSliceDefinitionGetAndUpdate(): void
    {
        $this->respond(Fixture::response(TimeSliceDefinition::class));
        self::assertInstanceOf(TimeSliceDefinition::class, $this->api->timeSliceDefinitions()->get('MUC', 'TS1'));

        $this->respondEmpty();
        $this->api->timeSliceDefinitions()->update('MUC', 'TS1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());
    }

    private function patch(): JsonPatch
    {
        return new JsonPatch()->replace('/name', 'x');
    }

    private function respondEmpty(): void
    {
        $this->httpClient->addResponse(new Response(204));
    }
}
