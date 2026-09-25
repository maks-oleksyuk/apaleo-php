<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\ExternalReference;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A settled payment as embedded in a folio; the full record (status, type, actions) is Payment\DTO\Payment. */
final readonly class FolioPayment
{
    public function __construct(
        public string $id,
        public PaymentMethod $method,
        public MonetaryValue $amount,
        public ?\DateTimeImmutable $paymentDate,
        public \DateTimeImmutable $businessDate,
        public ?ExternalReference $externalReference,
        public ?string $receipt,
        public ?string $sourcePaymentId,
        public ?string $depositEntryId,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            method: PaymentMethod::fromApi(ResponseData::string($data, 'method')),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            paymentDate: ResponseData::nullableDateTime($data, 'paymentDate'),
            businessDate: ResponseData::date($data, 'businessDate'),
            externalReference: ExternalReference::fromNested($data, 'externalReference'),
            receipt: ResponseData::nullableString($data, 'receipt'),
            sourcePaymentId: ResponseData::nullableString($data, 'sourcePaymentId'),
            depositEntryId: ResponseData::nullableString($data, 'depositEntryId'),
            movedFrom: EmbeddedFolio::fromNested($data, 'movedFrom'),
            movedTo: EmbeddedFolio::fromNested($data, 'movedTo'),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
        );
    }
}
