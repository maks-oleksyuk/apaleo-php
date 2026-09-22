<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountDetails;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;

final readonly class CreatePaymentAccountByStoredPaymentMethodRequest extends Request
{
    /** @param string $storedPaymentMethodId a specific stored method's id, or 'LATEST' for the payer's most recent one */
    public function __construct(
        private PaymentAccountTarget $target,
        private string $payerReference,
        private string $storedPaymentMethodId,
        private ?bool $isVirtual = null,
        private ?PaymentAccountDetails $accountDetails = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/payment-accounts/by-stored-payment-method';
    }

    public function body(): array
    {
        return array_filter([
            'target' => $this->target->toArray(),
            'payerReference' => $this->payerReference,
            'storedPaymentMethodId' => $this->storedPaymentMethodId,
            'isVirtual' => $this->isVirtual,
            'accountDetails' => $this->accountDetails?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
