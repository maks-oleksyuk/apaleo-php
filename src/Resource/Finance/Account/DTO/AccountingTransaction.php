<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\TransactionCommand;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * One double-entry booking. $reference is the folio (or house/booking) it belongs to, typed by $referenceType;
 * all transactions from one business operation share $entryGroupNumber.
 */
final readonly class AccountingTransaction
{
    public function __construct(
        public \DateTimeImmutable $timestamp,
        public \DateTimeImmutable $date,
        public ExportAccount $debitedAccount,
        public ExportAccount $creditedAccount,
        public TransactionCommand $command,
        public MonetaryValue $amount,
        public ?Receipt $receipt,
        public string $entryNumber,
        public string $entryGroupNumber,
        public string $reference,
        public FolioType $referenceType,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            timestamp: ResponseData::dateTime($data, 'timestamp'),
            date: ResponseData::date($data, 'date'),
            debitedAccount: ExportAccount::fromArray(ResponseData::nested($data, 'debitedAccount')),
            creditedAccount: ExportAccount::fromArray(ResponseData::nested($data, 'creditedAccount')),
            command: TransactionCommand::fromApi(ResponseData::string($data, 'command')),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            receipt: Receipt::fromNested($data, 'receipt'),
            entryNumber: ResponseData::string($data, 'entryNumber'),
            entryGroupNumber: ResponseData::string($data, 'entryGroupNumber'),
            reference: ResponseData::string($data, 'reference'),
            referenceType: FolioType::fromApi(ResponseData::string($data, 'referenceType')),
        );
    }
}
