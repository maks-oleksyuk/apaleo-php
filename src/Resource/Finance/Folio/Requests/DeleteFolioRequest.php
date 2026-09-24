<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteFolioRequest extends Request
{
    public function __construct(
        private string $folioId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId);
    }
}
