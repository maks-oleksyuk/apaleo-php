<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO;

use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountPayerInteraction;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PaymentAccount
{
    /** @param list<Action> $actions */
    public function __construct(
        public string $id,
        public PaymentAccountTarget $target,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
        public ?string $externalPaymentTransactionId,
        public PaymentAccountStatus $status,
        public ?string $failureReason,
        public PaymentAccountPayerInteraction $payerInteraction,
        public ?PaymentAccountLink $paymentLink,
        public ?PaymentAccountDetails $accountDetails,
        public bool $isVirtual,
        public array $actions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $externalReference = ResponseData::nested($data, 'externalReference');

        return new self(
            id: ResponseData::string($data, 'id'),
            target: PaymentAccountTarget::fromArray(ResponseData::nested($data, 'target')),
            created: ResponseData::dateTime($data, 'created'),
            updated: ResponseData::dateTime($data, 'updated'),
            externalPaymentTransactionId: $externalReference !== [] ? ResponseData::nullableString($externalReference, 'paymentTransactionId') : null,
            status: PaymentAccountStatus::fromApi(ResponseData::string($data, 'status')),
            failureReason: ResponseData::nullableString($data, 'failureReason'),
            payerInteraction: PaymentAccountPayerInteraction::fromApi(ResponseData::string($data, 'payerInteraction')),
            paymentLink: ResponseData::nullableNested($data, 'paymentLink', PaymentAccountLink::fromArray(...)),
            accountDetails: ResponseData::nullableNested($data, 'accountDetails', PaymentAccountDetails::fromArray(...)),
            isVirtual: ResponseData::bool($data, 'isVirtual'),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
