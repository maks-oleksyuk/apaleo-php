<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Account\DTO\ReplaceAccount;

final readonly class ReplaceCurrentAccountRequest extends Request
{
    public function __construct(
        private ReplaceAccount $account,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/account/v1/accounts/current';
    }

    public function body(): array
    {
        return $this->account->toArray();
    }
}
