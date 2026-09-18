<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\CreateUnit;

final class BulkCreateUnitsRequest extends Request
{
    protected Method $method = Method::POST;

    /**
     * @param list<CreateUnit> $units
     */
    public function __construct(
        private readonly array $units,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/units/bulk';
    }

    public function body(): array
    {
        return [
            'units' => array_map(static fn (CreateUnit $unit): array => $unit->toArray(), $this->units),
        ];
    }
}
