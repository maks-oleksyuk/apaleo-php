<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

/** Does not expose the deprecated `accountOwner` field; pass $paymentAccountId instead (required from 2026-02-15 onward). */
final readonly class CreateAuthorizationByPaymentAccountRequest extends Request
{
    public function __construct(
        private AuthorizationTarget $target,
        private MonetaryValue $amount,
        private ?string $paymentAccountId = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorizations/by-payment-account';
    }

    public function body(): array
    {
        return array_filter([
            'target' => $this->target->toArray(),
            'amount' => $this->amount->toArray(),
            'paymentAccountId' => $this->paymentAccountId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
