<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class RefreshAuthorizationRequest extends Request
{
    public function __construct(
        private string $authorizationId,
        private MonetaryValue $amount,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorization-actions/'.rawurlencode($this->authorizationId).'/refresh';
    }

    public function body(): array
    {
        return ['amount' => $this->amount->toArray()];
    }
}
