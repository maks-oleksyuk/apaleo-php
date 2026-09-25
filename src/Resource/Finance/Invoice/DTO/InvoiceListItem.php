<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceAction;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceCancellationReason;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceStatus;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceListItem
{
    /** @param list<InvoiceAction> $allowedActions only filled with expand: ['allowedActions'] */
    public function __construct(
        public string $id,
        public string $number,
        public InvoiceType $type,
        public ?string $series,
        public InvoiceStatus $status,
        public bool $paymentSettled,
        public \DateTimeImmutable $created,
        public string $folioId,
        public ?string $reservationId,
        public ?string $bookingId,
        public string $propertyId,
        public string $languageCode,
        public MonetaryValue $subTotal,
        public ?MonetaryValue $outstandingPayment,
        public ?string $guestName,
        public ?string $guestCompany,
        public ?EmbeddedCompany $company,
        public ?string $relatedInvoiceNumber,
        public ?string $writeOffReason,
        public ?InvoiceCancellationReason $cancellationReasonCode,
        public array $allowedActions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $cancellationReason = ResponseData::nullableString($data, 'cancellationReasonCode');

        return new self(
            id: ResponseData::string($data, 'id'),
            number: ResponseData::string($data, 'number'),
            type: InvoiceType::fromApi(ResponseData::string($data, 'type')),
            series: ResponseData::nullableString($data, 'series'),
            status: InvoiceStatus::fromApi(ResponseData::string($data, 'status')),
            paymentSettled: ResponseData::bool($data, 'paymentSettled'),
            created: ResponseData::dateTime($data, 'created'),
            folioId: ResponseData::string($data, 'folioId'),
            reservationId: ResponseData::nullableString($data, 'reservationId'),
            bookingId: ResponseData::nullableString($data, 'bookingId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            languageCode: ResponseData::string($data, 'languageCode'),
            subTotal: MonetaryValue::fromArray(ResponseData::nested($data, 'subTotal')),
            outstandingPayment: ResponseData::nullableNested($data, 'outstandingPayment', MonetaryValue::fromArray(...)),
            guestName: ResponseData::nullableString($data, 'guestName'),
            guestCompany: ResponseData::nullableString($data, 'guestCompany'),
            company: ResponseData::nullableNested($data, 'company', EmbeddedCompany::fromArray(...)),
            relatedInvoiceNumber: ResponseData::nullableString($data, 'relatedInvoiceNumber'),
            writeOffReason: ResponseData::nullableString($data, 'writeOffReason'),
            cancellationReasonCode: $cancellationReason !== null ? InvoiceCancellationReason::fromApi($cancellationReason) : null,
            allowedActions: array_map(InvoiceAction::fromApi(...), ResponseData::stringListOrEmpty($data, 'allowedActions')),
        );
    }
}
