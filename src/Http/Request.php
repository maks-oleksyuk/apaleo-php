<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

use Oleksyuk\Apaleo\Http\Enum\Method;

abstract class Request
{
    protected Method $method;

    abstract public function endpoint(): string;

    public function method(): Method
    {
        if (!isset($this->method)) {
            throw new \LogicException(sprintf(
                '%s is missing an HTTP method. Declare it as [protected Method $method = Method::GET;].',
                static::class,
            ));
        }

        return $this->method;
    }

    /** @return array<string, mixed> */
    public function query(): array
    {
        return [];
    }

    /** @return array<string, mixed>|null */
    public function body(): ?array
    {
        return null;
    }
}
