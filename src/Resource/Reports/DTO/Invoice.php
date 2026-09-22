<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Invoice
{
    /** @param list<TaxDetail> $taxDetails */
    public function __construct(
        public ?string $number,
        public \DateTimeImmutable $date,
        public MonetaryValue $subTotal,
        public ?MonetaryValue $outstandingPayment,
        public array $taxDetails,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $outstandingPayment = ResponseData::nested($data, 'outstandingPayment');

        return new self(
            number: ResponseData::nullableString($data, 'number'),
            date: ResponseData::date($data, 'date'),
            subTotal: MonetaryValue::fromArray(ResponseData::nested($data, 'subTotal')),
            outstandingPayment: [] !== $outstandingPayment ? MonetaryValue::fromArray($outstandingPayment) : null,
            taxDetails: array_map(TaxDetail::fromArray(...), ResponseData::nestedList($data, 'taxDetails')),
        );
    }
}
