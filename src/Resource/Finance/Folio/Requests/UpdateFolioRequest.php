<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateFolioRequest extends Request
{
    public function __construct(
        private string $folioId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
