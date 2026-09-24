<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

use Oleksyuk\Apaleo\Http\Enum\Method;

abstract readonly class Request
{
    abstract public function method(): Method;

    abstract public function endpoint(): string;

    /** @return array<string, mixed> */
    public function query(): array
    {
        return [];
    }

    /** Media type sent as Accept; only non-JSON for endpoints read with RequestPipeline::sendRaw(), e.g. PDFs. */
    public function accept(): string
    {
        return 'application/json';
    }

    /** @return array<string, string> extra headers, e.g. Idempotency-Key; Authorization/Accept/Content-Type are ignored */
    public function headers(): array
    {
        return [];
    }

    /** @return null|array<array-key, mixed> */
    public function body(): ?array
    {
        return null;
    }
}
