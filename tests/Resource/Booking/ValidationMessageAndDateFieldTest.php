<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\AuthorizationFilter;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationDateField;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferValidationMessage;
use Oleksyuk\Apaleo\Resource\Booking\Offer\Enum\OfferValidationMessageCode;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountDateField;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\PaymentAccountFilter;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationValidationMessage;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationValidationMessageCategory;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationValidationMessageCode;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationFilter;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Booking')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class ValidationMessageAndDateFieldTest extends TestCase
{
    public function testFiltersSerializeDateFieldAndValidationMessageCategory(): void
    {
        self::assertSame('Expiration', new AuthorizationFilter(dateField: AuthorizationDateField::Expiration)->toQuery()['dateField']);
        self::assertSame('Modification', new PaymentAccountFilter(dateField: PaymentAccountDateField::Modification)->toQuery()['dateField']);
        self::assertSame(
            'GuestLoyalty,AutoUnitAssignment',
            new ReservationFilter(validationMessageCategory: [ReservationValidationMessageCategory::GuestLoyalty, ReservationValidationMessageCategory::AutoUnitAssignment])->toQuery()['validationMessageCategory'],
        );
        self::assertArrayNotHasKey('dateField', new AuthorizationFilter()->toQuery());
    }

    public function testValidationMessagesParseToEnumsAndFallBackOnNewValues(): void
    {
        $message = ReservationValidationMessage::fromArray(['category' => 'OfferNotAvailable', 'code' => 'UnitMoved', 'message' => 'm']);
        self::assertSame(ReservationValidationMessageCategory::OfferNotAvailable, $message->category);
        self::assertSame(ReservationValidationMessageCode::UnitMoved, $message->code);

        $new = ReservationValidationMessage::fromArray(['category' => 'Brand', 'code' => 'New', 'message' => 'm']);
        self::assertSame(ReservationValidationMessageCategory::Unrecognized, $new->category);
        self::assertSame(ReservationValidationMessageCode::Unrecognized, $new->code);

        self::assertSame(OfferValidationMessageCode::RatesNotSet, OfferValidationMessage::fromArray(['code' => 'RatesNotSet', 'message' => 'm'])->code);
    }
}
