<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

use Oleksyuk\Apaleo\Http\Enum\Method;

abstract class Request
{
    abstract public function method(): Method;

    abstract public function endpoint(): string;

    /** @return array<string, mixed> */
    public function query(): array
    {
        return [];
    }

    /** @return null|array<array-key, mixed> */
    public function body(): ?array
    {
        return null;
    }
}
