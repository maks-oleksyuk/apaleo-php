<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class SetCurrentAccountLiveRequest extends Request
{
    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/account/v1/account-actions/current/set-live';
    }
}
