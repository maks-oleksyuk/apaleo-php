<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;

final class CreatePaymentAccountByLinkRequest extends Request
{
    public function __construct(
        private readonly PaymentAccountTarget $target,
        private readonly string $propertyId,
        private readonly string $countryCode,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly ?string $description = null,
        private readonly ?string $payerEmail = null,
        private readonly ?string $returnUrl = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/payment-accounts/by-link';
    }

    public function body(): array
    {
        return array_filter([
            'target' => $this->target->toArray(),
            'propertyId' => $this->propertyId,
            'countryCode' => $this->countryCode,
            'expiresAt' => $this->expiresAt->format(\DateTimeInterface::ATOM),
            'description' => $this->description,
            'payerEmail' => $this->payerEmail,
            'returnUrl' => $this->returnUrl,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
