<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioAction;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioStatus;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioWarning;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Folio
{
    /**
     * @param list<Charge>           $charges
     * @param list<TransitoryCharge> $transitoryCharges
     * @param list<FolioPayment>     $payments
     * @param list<PendingPayment>   $pendingPayments
     * @param list<Allowance>        $allowances
     * @param list<EmbeddedFolio>    $relatedFolios      only filled with expand: ['folios']
     * @param list<string>           $relatedInvoiceIds
     * @param list<FolioWarning>     $folioWarnings
     * @param list<FolioAction>      $allowedActions
     * @param ?float                 $allowedPayment     the most that can still be paid on this folio
     * @param ?float                 $maximumAllowance   the most that can still be granted as allowance
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
        public EmbeddedProperty $property,
        public MonetaryValue $balance,
        public bool $isMainFolio,
        public bool $isEmpty,
        public bool $checkedOutOnAccountsReceivable,
        public array $charges,
        public array $transitoryCharges,
        public array $payments,
        public array $pendingPayments,
        public array $allowances,
        public array $relatedFolios,
        public array $relatedInvoiceIds,
        public array $folioWarnings,
        public array $allowedActions,
        public ?float $allowedPayment,
        public ?float $maximumAllowance,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::nullableString($data, 'type');

        return new self(
            id: ResponseData::string($data, 'id'),
            type: $type !== null ? FolioType::fromApi($type) : null,
            status: FolioStatus::fromApi(ResponseData::string($data, 'status')),
            created: ResponseData::dateTime($data, 'created'),
            updated: ResponseData::dateTime($data, 'updated'),
            closingDate: ResponseData::nullableDate($data, 'closingDate'),
            debitor: ResponseData::nullableNested($data, 'debitor', FolioDebitor::fromArray(...)),
            reservationId: ResponseData::nullableString(ResponseData::nested($data, 'reservation'), 'id'),
            bookingId: ResponseData::nullableString($data, 'bookingId'),
            company: ResponseData::nullableNested($data, 'company', EmbeddedCompany::fromArray(...)),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            balance: MonetaryValue::fromArray(ResponseData::nested($data, 'balance')),
            isMainFolio: ResponseData::bool($data, 'isMainFolio'),
            isEmpty: ResponseData::bool($data, 'isEmpty'),
            checkedOutOnAccountsReceivable: ResponseData::bool($data, 'checkedOutOnAccountsReceivable'),
            charges: array_map(Charge::fromArray(...), ResponseData::nestedList($data, 'charges')),
            transitoryCharges: array_map(TransitoryCharge::fromArray(...), ResponseData::nestedList($data, 'transitoryCharges')),
            payments: array_map(FolioPayment::fromArray(...), ResponseData::nestedList($data, 'payments')),
            pendingPayments: array_map(PendingPayment::fromArray(...), ResponseData::nestedList($data, 'pendingPayments')),
            allowances: array_map(Allowance::fromArray(...), ResponseData::nestedList($data, 'allowances')),
            relatedFolios: array_map(EmbeddedFolio::fromArray(...), ResponseData::nestedList($data, 'relatedFolios')),
            relatedInvoiceIds: array_map(static fn (array $invoice): string => ResponseData::string($invoice, 'id'), ResponseData::nestedList($data, 'relatedInvoices')),
            folioWarnings: array_map(FolioWarning::fromApi(...), ResponseData::stringListOrEmpty($data, 'folioWarnings')),
            allowedActions: array_map(FolioAction::fromApi(...), ResponseData::stringListOrEmpty($data, 'allowedActions')),
            allowedPayment: ResponseData::nullableFloat($data, 'allowedPayment'),
            maximumAllowance: ResponseData::nullableFloat($data, 'maximumAllowance'),
        );
    }
}
