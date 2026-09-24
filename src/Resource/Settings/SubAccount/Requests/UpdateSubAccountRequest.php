<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateSubAccountRequest extends Request
{
    public function __construct(
        private string $subAccountId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/sub-accounts/'.rawurlencode($this->subAccountId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
