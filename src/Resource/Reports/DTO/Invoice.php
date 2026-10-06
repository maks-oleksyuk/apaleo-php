<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
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
            subTotal: ResponseData::requiredNested($data, 'subTotal', MonetaryValue::fromArray(...)),
            outstandingPayment: [] !== $outstandingPayment ? MonetaryValue::fromArray($outstandingPayment) : null,
            taxDetails: ResponseData::mapList($data, 'taxDetails', TaxDetail::fromArray(...)),
        );
    }
}
