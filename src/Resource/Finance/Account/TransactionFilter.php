<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account;

use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountingSchema;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountType;

/**
 * Criteria for the transaction exports and aggregations. The *Daily variants match $from/$to
 * against the business day (date only); the others against the exact timestamp.
 * $reference (a folio id) is only supported by the *Daily variants.
 */
final readonly class TransactionFilter
{
    public function __construct(
        public string $propertyId,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public ?string $reference = null,
        public ?string $accountNumber = null,
        public ?AccountType $accountType = null,
        public ?AccountingSchema $accountingSchema = null,
        public ?string $languageCode = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(bool $byBusinessDay): array
    {
        $format = $byBusinessDay ? 'Y-m-d' : \DateTimeInterface::ATOM;

        return array_filter([
            'propertyId' => $this->propertyId,
            'from' => $this->from->format($format),
            'to' => $this->to->format($format),
            'reference' => $byBusinessDay ? $this->reference : null,
            'accountNumber' => $this->accountNumber,
            'accountType' => $this->accountType?->value,
            'accountingSchema' => $this->accountingSchema?->value,
            'languageCode' => $this->languageCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
