<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\PayerInteraction;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Authorization
{
    /** @param list<Action> $actions */
    public function __construct(
        public string $id,
        public AuthorizationTarget $target,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
        public ?string $externalPaymentTransactionId,
        public MonetaryValue $amount,
        public ?MonetaryValue $remainingBalance,
        public AuthorizationStatus $status,
        public ?string $failureReason,
        public PayerInteraction $payerInteraction,
        public ?\DateTimeImmutable $expiresAt,
        public ?string $paymentLinkUrl,
        public array $actions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $externalReference = ResponseData::nested($data, 'externalReference');
        $remainingBalance = ResponseData::nested($data, 'remainingBalance');

        return new self(
            id: ResponseData::string($data, 'id'),
            target: AuthorizationTarget::fromArray(ResponseData::nested($data, 'target')),
            created: ResponseData::dateTime($data, 'created'),
            updated: ResponseData::dateTime($data, 'updated'),
            externalPaymentTransactionId: $externalReference !== [] ? ResponseData::nullableString($externalReference, 'paymentTransactionId') : null,
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            remainingBalance: $remainingBalance !== [] ? MonetaryValue::fromArray($remainingBalance) : null,
            status: AuthorizationStatus::fromApi(ResponseData::string($data, 'status')),
            failureReason: ResponseData::nullableString($data, 'failureReason'),
            payerInteraction: PayerInteraction::fromApi(ResponseData::string($data, 'payerInteraction')),
            expiresAt: ResponseData::nullableDateTime($data, 'expiresAt'),
            paymentLinkUrl: ResponseData::nullableString($data, 'paymentLinkUrl'),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
