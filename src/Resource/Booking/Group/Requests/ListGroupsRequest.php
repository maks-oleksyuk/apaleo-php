<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupFilter;

final class ListGroupsRequest extends Request
{
    /** @param list<string> $expand */
    public function __construct(
        private readonly GroupFilter $filter,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
        private readonly array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
