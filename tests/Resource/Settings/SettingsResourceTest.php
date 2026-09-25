<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Settings;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Enum\CapturePaymentMode;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CityTaxIgnoreRule;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CityTaxSubcategory;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CreateCityTax;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\CityTaxType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\RemittanceResponsibility;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\TaxHandlingType;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO\ReplaceInvoiceAddress;
use Oleksyuk\Apaleo\Resource\Settings\Language\DTO\ReplaceLanguage;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO\CreateMarketSegment;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO\CreateSubAccount;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO\CreateTimeSliceDefinition;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Settings')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class SettingsResourceTest extends SettingsTestCase
{
    public function testCapturePolicyListAndUpdate(): void
    {
        $this->respond(['count' => 1, 'capturePolicies' => [[
            'id' => 'MUC', 'code' => 'MUC', 'propertyId' => 'MUC', 'captureNoShowFee' => true, 'captureCancellationFee' => false,
            'capturePrepayment' => true, 'postOtaBankTransferOnCheckOut' => false, 'capturePayment' => 'CheckIn',
        ]]]);

        $result = $this->api->capturePolicies()->list('MUC', pageSize: 10);

        self::assertSame(CapturePaymentMode::CheckIn, $result[0]->capturePayment);
        self::assertTrue($result[0]->captureNoShowFee);
        self::assertStringEndsWith('/settings/v1/capture-policies?propertyId=MUC&pageSize=10', $this->lastUri());

        $this->respond([], 204);
        $this->api->capturePolicies()->update('MUC', new JsonPatch()->replace('/capturePayment', 'CheckOut'));

        self::assertSame('PATCH', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/settings/v1/capture-policies/MUC', $this->lastUri());
    }

    public function testGetCityTaxMapsFullModel(): void
    {
        $this->respond([
            'id' => 'MUC-CT', 'code' => 'CT', 'propertyId' => 'MUC',
            'name' => ['en' => 'City tax'], 'description' => ['en' => 'Per night'],
            'type' => 'PerPersonPerNight', 'taxHandlingType' => 'AfterTax', 'value' => 2.5, 'vatType' => 'Without',
            'priority' => 1, 'includeCityTaxInRateAmount' => false, 'limit' => 7,
            'subcategories' => [['name' => ['en' => 'Kids'], 'value' => 0, 'age' => ['min' => 0, 'max' => 12]]],
            'pricingRules' => [['value' => 1, 'maxPrice' => 50]],
            'ignoredFor' => [['distributionChannel' => ['channelCode' => 'BookingCom', 'remittanceResponsibility' => 'Ota']]],
        ]);

        $tax = $this->api->cityTaxes()->get('MUC-CT', ['en']);

        self::assertSame(['en' => 'City tax'], $tax->name);
        self::assertSame(CityTaxType::PerPersonPerNight, $tax->type);
        self::assertSame(2.5, $tax->value);
        self::assertSame(7, $tax->limit);
        self::assertSame(12, $tax->subcategories[0]->maxAge);
        self::assertSame(50.0, $tax->pricingRules[0]->maxPrice);
        self::assertSame(ChannelCode::BookingCom, $tax->ignoredFor[0]->channelCode);
        self::assertSame(RemittanceResponsibility::Ota, $tax->ignoredFor[0]->remittanceResponsibility);
        self::assertStringEndsWith('/settings/v1/city-tax/MUC-CT?languages=en', $this->lastUri());
    }

    public function testCityTaxListAndCreate(): void
    {
        $this->respond(['count' => 1, 'cityTaxes' => [[
            'id' => 'MUC-CT', 'code' => 'CT', 'propertyId' => 'MUC', 'name' => 'City tax', 'description' => 'Per night',
            'type' => 'PercentOfNet', 'taxHandlingType' => 'BeforeTax', 'value' => 5, 'vatType' => 'Normal',
            'priority' => 0, 'includeCityTaxInRateAmount' => true,
        ]]]);

        $result = $this->api->cityTaxes()->list('MUC');

        self::assertSame('City tax', $result[0]->name);
        self::assertSame(VatType::Normal, $result[0]->vatType);

        $this->respond(['id' => 'MUC-CT'], 201);
        $id = $this->api->cityTaxes()->create(new CreateCityTax(
            propertyId: 'MUC',
            name: ['en' => 'City tax'],
            description: ['en' => 'Per night'],
            type: CityTaxType::PerPersonPerNight,
            taxHandlingType: TaxHandlingType::AfterTax,
            value: 2.5,
            vatType: VatType::Without,
            subcategories: [new CityTaxSubcategory(['en' => 'Kids'], 0, 0, 12)],
            ignoredFor: [new CityTaxIgnoreRule(ChannelCode::BookingCom)],
        ));

        $body = $this->lastBody();
        self::assertSame('MUC-CT', $id);
        self::assertSame([['name' => ['en' => 'Kids'], 'value' => 0, 'age' => ['min' => 0, 'max' => 12]]], $body['subcategories']);
        self::assertSame([['distributionChannel' => ['channelCode' => 'BookingCom']]], $body['ignoredFor']);
        self::assertArrayNotHasKey('pricingRules', $body);
        self::assertArrayNotHasKey('code', $body);
    }

    public function testSubAccountFlow(): void
    {
        $this->respond(['count' => 1, 'subAccounts' => [['id' => 'MUC-ALCO', 'propertyId' => 'MUC', 'code' => 'ALCO', 'name' => 'Alcohol', 'type' => 'FoodAndBeverages']]]);

        $result = $this->api->subAccounts()->list('MUC');

        self::assertSame(ServiceType::FoodAndBeverages, $result[0]->type);
        self::assertStringEndsWith('/settings/v1/sub-accounts?propertyId=MUC', $this->lastUri());

        $this->respond(['count' => 3]);
        self::assertSame(3, $this->api->subAccounts()->count('MUC'));
        self::assertStringEndsWith('/settings/v1/sub-accounts/$count?propertyId=MUC', $this->lastUri());

        $this->respond([], 404);
        self::assertFalse($this->api->subAccounts()->exists('MUC-NOPE'));
        self::assertSame('HEAD', $this->lastRequest()->getMethod());

        $this->respond(['id' => 'MUC-ALCO'], 201);
        $this->api->subAccounts()->create(new CreateSubAccount('MUC', 'ALCO', 'Alcohol', ServiceType::FoodAndBeverages));
        self::assertSame(['propertyId' => 'MUC', 'code' => 'ALCO', 'name' => 'Alcohol', 'type' => 'FoodAndBeverages'], $this->lastBody());
    }

    public function testFeatureAndPropertySettings(): void
    {
        $this->respond([
            'areCustomRevenueSubAccountsEnabled' => true, 'performAccountingForOpenInvoiceActions' => false,
            'showRecipientForEachLineItemOnTheInvoice' => true, 'invoiceNumberPattern' => 'INV-{yyyy}-{0000}',
            'advanceInvoiceNumberPattern' => 'ADV-{0000}',
        ]);

        $features = $this->api->features()->get('MUC');

        self::assertTrue($features->areCustomRevenueSubAccountsEnabled);
        self::assertNull($features->maxAmountForMinimalInvoices);
        self::assertStringEndsWith('/settings/v1/features/MUC', $this->lastUri());

        $this->respond(['timeZone' => 'Europe/Berlin', 'currency' => 'EUR']);
        $settings = $this->api->properties()->get('MUC');

        self::assertSame('Europe/Berlin', $settings->timeZone);
        self::assertStringEndsWith('/settings/v1/properties/MUC', $this->lastUri());
    }

    public function testInvoiceAddressListAndReplace(): void
    {
        $this->respond(['count' => 1, 'addresses' => [['propertyId' => 'MUC', 'addressLine1' => 'Main 1', 'postalCode' => '80331', 'city' => 'Munich', 'countryCode' => 'DE']]]);

        $result = $this->api->invoiceAddresses()->list(['MUC', 'BER']);

        self::assertSame('Munich', $result[0]->city);
        self::assertNull($result[0]->addressLine2);
        self::assertStringEndsWith('/settings/v1/invoice-address?propertyIds=MUC,BER', $this->lastUri());

        $this->respond([], 204);
        $this->api->invoiceAddresses()->replace(['MUC'], new ReplaceInvoiceAddress('Main 1', '80331', 'Munich', 'DE'));

        self::assertSame('PUT', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/settings/v1/invoice-address?propertyIds=MUC', $this->lastUri());
        self::assertSame(['addressLine1' => 'Main 1', 'postalCode' => '80331', 'city' => 'Munich', 'countryCode' => 'DE'], $this->lastBody());
    }

    public function testLanguagesListAndReplace(): void
    {
        $this->respond(['languages' => [['code' => 'en', 'default' => true, 'mandatory' => true], ['code' => 'de', 'default' => false, 'mandatory' => false]]]);

        $languages = $this->api->languages()->list();

        self::assertCount(2, $languages);
        self::assertTrue($languages[0]->default);

        $this->respond([], 204);
        $this->api->languages()->replace([new ReplaceLanguage('en', true)]);

        self::assertSame(['languages' => [['code' => 'en', 'mandatory' => true]]], $this->lastBody());
    }

    public function testMarketSegmentFlow(): void
    {
        $this->respond(['count' => 1, 'marketSegments' => [['id' => 'MUC-LEI', 'code' => 'LEI', 'name' => 'Leisure']]]);

        $result = $this->api->marketSegments()->list(['MUC'], pageSize: 5);

        self::assertSame('Leisure', $result[0]->name);
        self::assertSame([], $result[0]->propertyIds);
        self::assertStringEndsWith('/settings/v1/market-segments?propertyIds=MUC&pageSize=5', $this->lastUri());

        $this->respond([], 200);
        self::assertTrue($this->api->marketSegments()->exists('MUC-LEI'));

        $this->respond(['id' => 'LEI'], 201);
        $this->api->marketSegments()->create(new CreateMarketSegment('LEI', 'Leisure', propertyIds: ['MUC']));

        self::assertSame(['code' => 'LEI', 'name' => 'Leisure', 'propertyIds' => ['MUC']], $this->lastBody());
    }

    public function testTimeSliceDefinitionFlow(): void
    {
        $this->respond(['count' => 1, 'timeSliceDefinitions' => [[
            'id' => 'MUC-NIGHT', 'name' => 'Overnight', 'template' => 'OverNight', 'checkInTime' => '15:00:00', 'checkOutTime' => '11:00:00', 'isUsed' => true,
            'actions' => [['action' => 'Delete', 'isAllowed' => false, 'reasons' => [['code' => 'DeleteIsNotAllowedForUsedTimeSliceDefinition', 'message' => 'In use']]]],
        ]]]);

        $result = $this->api->timeSliceDefinitions()->list('MUC', ['actions']);

        self::assertSame(TimeSliceTemplate::OverNight, $result[0]->template);
        self::assertFalse($result[0]->actions[0]->isAllowed);
        self::assertStringEndsWith('/settings/v1/properties/MUC/time-slice-definitions?expand=actions', $this->lastUri());

        $this->respond(['id' => 'MUC-DAY'], 201);
        $id = $this->api->timeSliceDefinitions()->create('MUC', new CreateTimeSliceDefinition('Day use', TimeSliceTemplate::DayUse, '08:00:00', '18:00:00'));

        self::assertSame('MUC-DAY', $id);
        self::assertSame(['name' => 'Day use', 'template' => 'DayUse', 'checkInTime' => '08:00:00', 'checkOutTime' => '18:00:00'], $this->lastBody());

        $this->respond([], 204);
        $this->api->timeSliceDefinitions()->delete('MUC', 'MUC-DAY');

        self::assertSame('DELETE', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/settings/v1/properties/MUC/time-slice-definitions/MUC-DAY', $this->lastUri());
    }
}
