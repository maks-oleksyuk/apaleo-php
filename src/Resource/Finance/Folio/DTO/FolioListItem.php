<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioAction;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioStatus;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioWarning;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class FolioListItem
{
    /**
     * @param list<Charge>           $charges
     * @param list<TransitoryCharge> $transitoryCharges
     * @param list<FolioPayment>     $payments
     * @param list<Allowance>        $allowances
     * @param list<string>           $relatedInvoiceIds
     * @param list<FolioWarning>     $folioWarnings
     * @param list<FolioAction>      $allowedActions
     */
    public function __construct(
        public string $id,
        public ?FolioType $type,
        public FolioStatus $status,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
        public ?\DateTimeImmutable $closingDate,
        public ?FolioDebitor $debitor,
        public ?string $reservationId,
        public ?string $bookingId,
        public ?EmbeddedCompany $company,
        public MonetaryValue $balance,
        public bool $isMainFolio,
        public bool $isEmpty,
        public bool $checkedOutOnAccountsReceivable,
        public array $charges,
        public array $transitoryCharges,
        public array $payments,
        public array $allowances,
        public array $relatedInvoiceIds,
        public array $folioWarnings,
        public array $allowedActions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::nullableString($data, 'type');
        $company = ResponseData::nested($data, 'company');

        return new self(
            id: ResponseData::string($data, 'id'),
            type: $type !== null ? FolioType::fromApi($type) : null,
            status: FolioStatus::fromApi(ResponseData::string($data, 'status')),
            created: ResponseData::dateTime($data, 'created'),
            updated: ResponseData::dateTime($data, 'updated'),
            closingDate: ResponseData::nullableDate($data, 'closingDate'),
            debitor: FolioDebitor::fromNested($data, 'debitor'),
            reservationId: ResponseData::nullableString(ResponseData::nested($data, 'reservation'), 'id'),
            bookingId: ResponseData::nullableString($data, 'bookingId'),
            company: $company !== [] ? EmbeddedCompany::fromArray($company) : null,
            balance: MonetaryValue::fromArray(ResponseData::nested($data, 'balance')),
            isMainFolio: ResponseData::bool($data, 'isMainFolio'),
            isEmpty: ResponseData::bool($data, 'isEmpty'),
            checkedOutOnAccountsReceivable: ResponseData::bool($data, 'checkedOutOnAccountsReceivable'),
            charges: array_map(Charge::fromArray(...), ResponseData::nestedList($data, 'charges')),
            transitoryCharges: array_map(TransitoryCharge::fromArray(...), ResponseData::nestedList($data, 'transitoryCharges')),
            payments: array_map(FolioPayment::fromArray(...), ResponseData::nestedList($data, 'payments')),
            allowances: array_map(Allowance::fromArray(...), ResponseData::nestedList($data, 'allowances')),
            relatedInvoiceIds: array_map(static fn (array $invoice): string => ResponseData::string($invoice, 'id'), ResponseData::nestedList($data, 'relatedInvoices')),
            folioWarnings: array_map(FolioWarning::fromApi(...), ResponseData::stringListOrEmpty($data, 'folioWarnings')),
            allowedActions: array_map(FolioAction::fromApi(...), ResponseData::stringListOrEmpty($data, 'allowedActions')),
        );
    }
}
