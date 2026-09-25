<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class CreateAuthorizationByLinkRequest extends Request
{
    public function __construct(
        private AuthorizationTarget $target,
        private MonetaryValue $amount,
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
        return '/booking/v1/authorizations/by-link';
    }

    public function body(): array
    {
        return array_filter([
            'target' => $this->target->toArray(),
            'amount' => $this->amount->toArray(),
            'countryCode' => $this->countryCode,
            'expiresAt' => $this->expiresAt->format(\DateTimeInterface::ATOM),
            'description' => $this->description,
            'payerEmail' => $this->payerEmail,
            'returnUrl' => $this->returnUrl,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
