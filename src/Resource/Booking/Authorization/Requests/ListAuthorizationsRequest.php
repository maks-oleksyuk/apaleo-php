<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\AuthorizationFilter;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListAuthorizationsRequest extends Request
{
    /**
     * @param list<'created:asc'|'created:desc'|'expiresat:asc'|'expiresat:desc'|'updated:asc'|'updated:desc'> $sort
     * @param list<'actions'|'remainingBalance'> $expand
     */
    public function __construct(
        private AuthorizationFilter $filter,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $sort = [],
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/authorizations';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'sort' => Query::csv($this->sort),
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
