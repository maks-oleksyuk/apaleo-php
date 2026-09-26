<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class GetGroupRequest extends Request
{
    /** @param list<'actions'|'blocks'> $expand */
    public function __construct(
        private string $groupId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups/'.rawurlencode($this->groupId);
    }

    public function query(): array
    {
        return array_filter(['expand' => Query::csv($this->expand)], static fn (mixed $value): bool => $value !== null);
    }
}
