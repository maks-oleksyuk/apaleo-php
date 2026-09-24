<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\FolioFilter;

final readonly class ListFoliosRequest extends Request
{
    /**
     * @param list<'balance:asc'|'balance:desc'|'created:asc'|'created:desc'> $sort
     * @param list<'allowances'|'allowedActions'|'charges'|'company'|'payments'|'transitoryCharges'|'warnings'> $expand
     */
    public function __construct(
        private FolioFilter $filter = new FolioFilter(),
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
        return '/finance/v1/folios';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'sort' => implode(',', $this->sort) ?: null,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
