<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

/** Creates an authorization from an external payment transaction (e.g. an OTA-collected card) referenced by $transactionReference. */
final readonly class CreateAuthorizationByAuthorizationRequest extends Request
{
    public function __construct(
        private AuthorizationTarget $target,
        private MonetaryValue $amount,
        private string $transactionReference,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorizations/by-authorization';
    }

    public function body(): array
    {
        return [
            'target' => $this->target->toArray(),
            'amount' => $this->amount->toArray(),
            'transactionReference' => $this->transactionReference,
        ];
    }
}
