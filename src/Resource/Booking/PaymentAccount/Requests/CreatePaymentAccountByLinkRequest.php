<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;

final readonly class CreatePaymentAccountByLinkRequest extends Request
{
    public function __construct(
        private PaymentAccountTarget $target,
        private string $propertyId,
        private string $countryCode,
        private \DateTimeImmutable $expiresAt,
        private ?string $description = null,
        private ?string $payerEmail = null,
        private ?string $returnUrl = null,
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
