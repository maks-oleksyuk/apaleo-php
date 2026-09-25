<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoicePayment
{
    /** @param string $methodName the method as printed on the invoice, in the invoice's language */
    public function __construct(
        public string $id,
        public PaymentMethod $method,
        public string $methodName,
        public MonetaryValue $amount,
        public ?\DateTimeImmutable $paymentDate,
        public ?\DateTimeImmutable $businessDate,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            method: PaymentMethod::fromApi(ResponseData::string($data, 'method')),
            methodName: ResponseData::string($data, 'methodName'),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            paymentDate: ResponseData::nullableDateTime($data, 'paymentDate'),
            businessDate: ResponseData::nullableDate($data, 'businessDate'),
        );
    }
}
