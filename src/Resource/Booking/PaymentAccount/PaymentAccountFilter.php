<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountPayerInteraction;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountStatus;
use Oleksyuk\Apaleo\Support\Query;

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
            'paymentAccountIds' => Query::csv($this->paymentAccountIds),
            'propertyIds' => Query::csv($this->propertyIds),
            'bookingIds' => Query::csv($this->bookingIds),
            'reservationIds' => Query::csv($this->reservationIds),
            'payerInteractions' => Query::csv($this->payerInteractions),
            'status' => Query::csv($this->status),
            'targetTypes' => Query::csv($this->targetTypes),
            'dateField' => $this->dateField,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'paymentLinkUrls' => Query::csv($this->paymentLinkUrls),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
