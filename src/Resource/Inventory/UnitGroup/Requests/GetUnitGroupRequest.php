<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetUnitGroupRequest extends Request
{
    /** @param ?list<string> $languages */
    public function __construct(
        private string $unitGroupId,
        private ?array $languages = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-groups/'.rawurlencode($this->unitGroupId);
    }

    public function query(): array
    {
        return array_filter([
            'languages' => $this->languages !== null ? implode(',', $this->languages) : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
