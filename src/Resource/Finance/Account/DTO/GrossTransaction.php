<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\TransactionCommand;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A transaction with its VAT folded in instead of booked separately. */
final readonly class GrossTransaction
{
    /** @param list<TaxAmount> $taxes */
    public function __construct(
        public \DateTimeImmutable $timestamp,
        public \DateTimeImmutable $date,
        public ExportAccount $debitedAccount,
        public ExportAccount $creditedAccount,
        public TransactionCommand $command,
        public string $currency,
        public float $grossAmount,
        public float $netAmount,
        public array $taxes,
        public Receipt $receipt,
        public string $sourceEntryNumber,
        public string $reference,
        public FolioType $referenceType,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            timestamp: ResponseData::dateTime($data, 'timestamp'),
            date: ResponseData::date($data, 'date'),
            debitedAccount: ResponseData::requiredNested($data, 'debitedAccount', ExportAccount::fromArray(...)),
            creditedAccount: ResponseData::requiredNested($data, 'creditedAccount', ExportAccount::fromArray(...)),
            command: TransactionCommand::fromApi(ResponseData::string($data, 'command')),
            currency: ResponseData::string($data, 'currency'),
            grossAmount: ResponseData::float($data, 'grossAmount'),
            netAmount: ResponseData::float($data, 'netAmount'),
            taxes: ResponseData::mapList($data, 'taxes', TaxAmount::fromArray(...)),
            receipt: ResponseData::requiredNested($data, 'receipt', Receipt::fromArray(...)),
            sourceEntryNumber: ResponseData::string($data, 'sourceEntryNumber'),
            reference: ResponseData::string($data, 'reference'),
            referenceType: FolioType::fromApi(ResponseData::string($data, 'referenceType')),
        );
    }
}
