<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\UnitFilter;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListUnitsRequest extends Request
{
    /** @param list<'actions'|'connectedUnits'|'property'|'unitGroup'> $expand */
    public function __construct(
        private UnitFilter $filter = new UnitFilter(),
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
