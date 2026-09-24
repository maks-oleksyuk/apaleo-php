<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\FolioFilter;

final readonly class CountFoliosRequest extends Request
{
    public function __construct(
        private FolioFilter $filter = new FolioFilter(),
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
