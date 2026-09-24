<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A split refunds the original payment and posts two new payments in its place. */
final readonly class SplitPaymentResult
{
    public function __construct(
        public ?string $refundId,
        public ?string $firstPaymentId,
        public ?string $secondPaymentId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            refundId: ResponseData::nullableString($data, 'refundId'),
            firstPaymentId: ResponseData::nullableString($data, 'firstPaymentId'),
            secondPaymentId: ResponseData::nullableString($data, 'secondPaymentId'),
        );
    }
}
