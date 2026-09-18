<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class BulkUpdateUnitsRequest extends Request
{
    /**
     * @param list<string> $unitIds
     */
    public function __construct(
        private readonly array $unitIds,
        private readonly JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units';
    }

    public function query(): array
    {
        return ['unitIds' => implode(',', $this->unitIds)];
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
