<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountPayerInteraction;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountStatus;

final readonly class PaymentAccountFilter
{
    /**
     * @param list<string>                        $paymentAccountIds
     * @param list<string>                        $propertyIds
     * @param list<string>                        $bookingIds
     * @param list<string>                        $reservationIds
     * @param list<PaymentAccountPayerInteraction> $payerInteractions
     * @param list<PaymentAccountStatus>           $status
     * @param list<AuthorizationTargetType>        $targetTypes
     * @param list<string>                         $paymentLinkUrls
     */
    public function __construct(
        public array $paymentAccountIds = [],
        public array $propertyIds = [],
        public array $bookingIds = [],
        public array $reservationIds = [],
        public array $payerInteractions = [],
        public array $status = [],
        public array $targetTypes = [],
        public ?string $dateField = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        public array $paymentLinkUrls = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'paymentAccountIds' => implode(',', $this->paymentAccountIds) ?: null,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'bookingIds' => implode(',', $this->bookingIds) ?: null,
            'reservationIds' => implode(',', $this->reservationIds) ?: null,
            'payerInteractions' => implode(',', array_map(static fn (PaymentAccountPayerInteraction $p): string => $p->value, $this->payerInteractions)) ?: null,
            'status' => implode(',', array_map(static fn (PaymentAccountStatus $s): string => $s->value, $this->status)) ?: null,
            'targetTypes' => implode(',', array_map(static fn (AuthorizationTargetType $t): string => $t->value, $this->targetTypes)) ?: null,
            'dateField' => $this->dateField,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'paymentLinkUrls' => implode(',', $this->paymentLinkUrls) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
