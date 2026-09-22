<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class DeleteGroupRequest extends Request
{
    public function __construct(
        private readonly string $groupId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups/'.rawurlencode($this->groupId);
    }
}
