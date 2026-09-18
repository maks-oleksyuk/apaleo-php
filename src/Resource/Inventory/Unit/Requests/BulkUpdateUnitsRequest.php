<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class BulkUpdateUnitsRequest extends Request
{
    protected Method $method = Method::PATCH;

    /**
     * @param list<string> $unitIds
     */
    public function __construct(
        private readonly array $unitIds,
        private readonly JsonPatch $patch,
    ) {}

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
