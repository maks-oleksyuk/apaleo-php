<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Enum\PaymentType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\ExternalReference;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentFailureCode;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * $method stays null until a payment link or terminal payment has actually been paid.
 * $expiresAt, $description and $url are only set for payment links.
 */
final readonly class Payment
{
    /** @param list<Action> $actions only filled with expand: ['actions'] */
    public function __construct(
        public string $id,
        public PaymentType $type,
        public PaymentStatus $status,
        public ?PaymentMethod $method,
        public MonetaryValue $amount,
        public \DateTimeImmutable $paymentDate,
        public \DateTimeImmutable $businessDate,
        public ?ExternalReference $externalReference,
        public ?string $receipt,
        public ?string $failureReason,
        public ?PaymentFailureCode $failureCode,
        public ?\DateTimeImmutable $expiresAt,
        public ?string $description,
        public ?string $url,
        public ?string $sourcePaymentId,
        public ?string $depositEntryId,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
        public array $actions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $method = ResponseData::nullableString($data, 'method');
        $failureCode = ResponseData::nullableString($data, 'failureCode');

        return new self(
            id: ResponseData::string($data, 'id'),
            type: PaymentType::fromApi(ResponseData::string($data, 'type')),
            status: PaymentStatus::fromApi(ResponseData::string($data, 'status')),
            method: $method !== null ? PaymentMethod::fromApi($method) : null,
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            paymentDate: ResponseData::dateTime($data, 'paymentDate'),
            businessDate: ResponseData::date($data, 'businessDate'),
            externalReference: ExternalReference::fromNested($data, 'externalReference'),
            receipt: ResponseData::nullableString($data, 'receipt'),
            failureReason: ResponseData::nullableString($data, 'failureReason'),
            failureCode: $failureCode !== null ? PaymentFailureCode::fromApi($failureCode) : null,
            expiresAt: ResponseData::nullableDateTime($data, 'expiresAt'),
            description: ResponseData::nullableString($data, 'description'),
            url: ResponseData::nullableString($data, 'url'),
            sourcePaymentId: ResponseData::nullableString($data, 'sourcePaymentId'),
            depositEntryId: ResponseData::nullableString($data, 'depositEntryId'),
            movedFrom: EmbeddedFolio::fromNested($data, 'movedFrom'),
            movedTo: EmbeddedFolio::fromNested($data, 'movedTo'),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
