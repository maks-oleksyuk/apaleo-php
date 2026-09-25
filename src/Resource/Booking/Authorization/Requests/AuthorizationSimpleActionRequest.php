<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Shared shape for the authorization-actions endpoints that take no body: cancel, expire-payment-link. */
final readonly class AuthorizationSimpleActionRequest extends Request
{
    /** @param 'cancel'|'expire-payment-link' $action */
    public function __construct(
        private string $authorizationId,
        private string $action,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorization-actions/'.rawurlencode($this->authorizationId).'/'.$this->action;
    }
}
