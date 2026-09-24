<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class SubAccountExistsRequest extends Request
{
    public function __construct(
        private string $subAccountId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/settings/v1/sub-accounts/'.rawurlencode($this->subAccountId);
    }
}
