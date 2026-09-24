<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateAccountPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateAuthorizationPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateCustomPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreatePaymentLink;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateTerminalPayment;

/** One class for the five ways to take a payment; the endpoint follows from the type of $data. */
final readonly class CreatePaymentRequest extends Request
{
    public function __construct(
        private string $folioId,
        private CreateAccountPayment|CreateAuthorizationPayment|CreateCustomPayment|CreatePaymentLink|CreateTerminalPayment $data,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId).'/payments'.match (true) {
            $this->data instanceof CreateCustomPayment => '',
            $this->data instanceof CreateTerminalPayment => '/by-terminal',
            $this->data instanceof CreateAuthorizationPayment => '/by-authorization',
            $this->data instanceof CreateAccountPayment => '/by-payment-account',
            $this->data instanceof CreatePaymentLink => '/by-link',
        };
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
