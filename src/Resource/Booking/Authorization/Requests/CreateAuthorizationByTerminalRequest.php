<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class CreateAuthorizationByTerminalRequest extends Request
{
    public function __construct(
        private AuthorizationTarget $target,
        private MonetaryValue $amount,
        private string $terminalId,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorizations/by-terminal';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return [
            'target' => $this->target->toArray(),
            'amount' => $this->amount->toArray(),
            'terminalId' => $this->terminalId,
        ];
    }
}
