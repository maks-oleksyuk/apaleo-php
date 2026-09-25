<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Oleksyuk\Apaleo\Resource\RatePlan\Company\CompanyFilter;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\CompanyAddress;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\CompanyRatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\CreateCompany;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Enum\InvoiceNetwork;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO\CreateService;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO\ServiceAvailability;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum\AvailabilityMode;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\ServiceFilter;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\PricingUnit;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;

/**
 * @internal
 *
 * @coversNothing
 */
final class ServiceAndCompanyResourceTest extends RatePlanTestCase
{
    public function testGetServiceMapsFullModel(): void
    {
        $this->respond([
            'id' => 'MUC-BRKF',
            'code' => 'BRKF',
            'name' => ['en' => 'Breakfast'],
            'description' => ['en' => 'Buffet'],
            'defaultGrossPrice' => ['amount' => 15, 'currency' => 'EUR'],
            'pricingUnit' => 'Person',
            'postNextDay' => true,
            'availability' => ['mode' => 'Daily', 'daysOfWeek' => ['Monday']],
            'property' => ['id' => 'MUC'],
            'accountingConfigs' => [['serviceType' => 'FoodAndBeverages', 'vatType' => 'Reduced', 'validFrom' => '2026-01-01']],
        ]);

        $service = $this->api->services()->get('MUC-BRKF');

        self::assertSame(PricingUnit::Person, $service->pricingUnit);
        self::assertSame(AvailabilityMode::Daily, $service->availability->mode);
        self::assertSame(ServiceType::FoodAndBeverages, $service->accountingConfigs[0]->serviceType);
    }

    public function testListServicesMapsFlattenedAccountingAndSendsFilter(): void
    {
        $this->respond(['count' => 1, 'services' => [[
            'id' => 'MUC-BRKF', 'code' => 'BRKF', 'name' => 'Breakfast', 'description' => 'Buffet',
            'defaultGrossPrice' => ['amount' => 15, 'currency' => 'EUR'], 'postNextDay' => true,
            'serviceType' => 'FoodAndBeverages', 'vatType' => 'Reduced',
            'availability' => ['mode' => 'Daily'], 'property' => ['id' => 'MUC'],
        ]]]);

        $result = $this->api->services()->list(new ServiceFilter(propertyId: 'MUC', onlySoldAsExtras: true, serviceTypes: [ServiceType::FoodAndBeverages]));

        self::assertSame(VatType::Reduced, $result[0]->vatType);
        self::assertSame('Breakfast', $result[0]->name);
        self::assertStringContainsString('propertyId=MUC&onlySoldAsExtras=true&serviceTypes=FoodAndBeverages', $this->lastUri());
    }

    public function testCreateServiceSendsBody(): void
    {
        $this->respond(['id' => 'MUC-BRKF'], 201);

        $this->api->services()->create(new CreateService(
            code: 'BRKF',
            propertyId: 'MUC',
            name: ['en' => 'Breakfast'],
            description: ['en' => 'Buffet'],
            defaultGrossPrice: new MonetaryValue(15.0, 'EUR'),
            pricingUnit: PricingUnit::Person,
            postNextDay: true,
            availability: new ServiceAvailability(AvailabilityMode::Daily),
            accountingConfigs: [new AccountingConfig(ServiceType::FoodAndBeverages, VatType::Reduced, new \DateTimeImmutable('2026-01-01'))],
        ));

        $body = $this->lastBody();
        self::assertSame(['mode' => 'Daily'], $body['availability']);
        self::assertSame([['serviceType' => 'FoodAndBeverages', 'vatType' => 'Reduced', 'validFrom' => '2026-01-01']], $body['accountingConfigs']);
        self::assertArrayNotHasKey('channelCodes', $body);
    }

    public function testCompanyRoundTrip(): void
    {
        $this->respond(['count' => 1, 'companies' => [[
            'id' => 'MUC-ACME', 'code' => 'ACME', 'propertyId' => 'MUC', 'name' => 'Acme',
            'address' => ['addressLine1' => 'Main 1', 'postalCode' => '80331', 'city' => 'Munich', 'countryCode' => 'DE'],
            'canCheckOutOnAr' => true,
            'invoiceNetworkIdentity' => ['network' => 'Peppol', 'id' => '0088:123'],
            'ratePlans' => [['id' => 'MUC-CORP', 'code' => 'CORP', 'corporateCode' => 'ACME1', 'name' => 'Corporate']],
        ]]]);

        $companies = $this->api->companies()->list(new CompanyFilter(corporateCodes: ['ACME1'], textSearch: 'Ac'));

        self::assertSame(InvoiceNetwork::Peppol, $companies[0]->invoiceNetworkIdentity?->network);
        self::assertSame('ACME1', $companies[0]->ratePlans[0]->corporateCode);
        self::assertStringContainsString('corporateCodes=ACME1&textSearch=Ac', $this->lastUri());

        $this->respond(['id' => 'MUC-ACME'], 201);
        $this->api->companies()->create(new CreateCompany(
            code: 'ACME',
            propertyId: 'MUC',
            name: 'Acme',
            address: new CompanyAddress('Main 1', '80331', 'Munich', 'DE'),
            canCheckOutOnAr: false,
            ratePlans: [new CompanyRatePlan('MUC-CORP', 'ACME1')],
        ));

        $body = $this->lastBody();
        self::assertFalse($body['canCheckOutOnAr']);
        self::assertSame([['id' => 'MUC-CORP', 'corporateCode' => 'ACME1']], $body['ratePlans']);
    }
}
