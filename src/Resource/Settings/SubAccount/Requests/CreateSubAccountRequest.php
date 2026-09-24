<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO\CreateSubAccount;

final readonly class CreateSubAccountRequest extends Request
{
    public function __construct(
        private CreateSubAccount $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/settings/v1/sub-accounts';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
