<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class AddChargeAllowanceRequest extends Request
{
    public function __construct(
        private string $folioId,
        private string $chargeId,
        private string $reason,
        private MonetaryValue $amount,
        private ?\DateTimeImmutable $businessDate = null,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/charges/'.rawurlencode($this->chargeId).'/allowances';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return array_filter([
            'reason' => $this->reason,
            'amount' => $this->amount->toArray(),
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
