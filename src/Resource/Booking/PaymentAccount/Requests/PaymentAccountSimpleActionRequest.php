<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Shared shape for the payment-account-actions endpoints that take no body: cancel, expire-payment-link. */
final readonly class PaymentAccountSimpleActionRequest extends Request
{
    /** @param 'cancel'|'expire-payment-link' $action */
    public function __construct(
        private string $paymentAccountId,
        private string $action,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/payment-account-actions/'.rawurlencode($this->paymentAccountId).'/'.$this->action;
    }
}
