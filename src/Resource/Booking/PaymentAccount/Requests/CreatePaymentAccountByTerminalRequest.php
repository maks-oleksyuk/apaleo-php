<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;

final class CreatePaymentAccountByTerminalRequest extends Request
{
    public function __construct(
        private readonly PaymentAccountTarget $target,
        private readonly string $propertyId,
        private readonly string $terminalId,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/payment-accounts/by-terminal';
    }

    public function body(): array
    {
        return [
            'target' => $this->target->toArray(),
            'propertyId' => $this->propertyId,
            'terminalId' => $this->terminalId,
        ];
    }
}
