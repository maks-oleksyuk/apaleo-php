<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Refund\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\ExternalReference;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentFailureCode;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** $sourcePaymentId is set when the refund was made against one specific payment. */
final readonly class Refund
{
    public function __construct(
        public string $id,
        public PaymentStatus $status,
        public PaymentMethod $method,
        public MonetaryValue $amount,
        public \DateTimeImmutable $refundDate,
        public \DateTimeImmutable $businessDate,
        public ?string $reason,
        public ?ExternalReference $externalReference,
        public ?string $receipt,
        public ?string $sourcePaymentId,
        public ?string $failureReason,
        public ?PaymentFailureCode $failureCode,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $failureCode = ResponseData::nullableString($data, 'failureCode');

        return new self(
            id: ResponseData::string($data, 'id'),
            status: PaymentStatus::fromApi(ResponseData::string($data, 'status')),
            method: PaymentMethod::fromApi(ResponseData::string($data, 'method')),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            refundDate: ResponseData::dateTime($data, 'refundDate'),
            businessDate: ResponseData::date($data, 'businessDate'),
            reason: ResponseData::nullableString($data, 'reason'),
            externalReference: ExternalReference::fromNested($data, 'externalReference'),
            receipt: ResponseData::nullableString($data, 'receipt'),
            sourcePaymentId: ResponseData::nullableString($data, 'sourcePaymentId'),
            failureReason: ResponseData::nullableString($data, 'failureReason'),
            failureCode: $failureCode !== null ? PaymentFailureCode::fromApi($failureCode) : null,
            movedFrom: EmbeddedFolio::fromNested($data, 'movedFrom'),
            movedTo: EmbeddedFolio::fromNested($data, 'movedTo'),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
        );
    }
}
