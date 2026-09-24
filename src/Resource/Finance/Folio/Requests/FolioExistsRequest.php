<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class FolioExistsRequest extends Request
{
    public function __construct(
        private string $folioId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId);
    }
}
