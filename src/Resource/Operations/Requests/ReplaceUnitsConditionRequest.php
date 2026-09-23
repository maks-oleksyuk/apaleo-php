<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Operations\DTO\UnitConditionUpdate;

final readonly class ReplaceUnitsConditionRequest extends Request
{
    /** @param list<UnitConditionUpdate> $conditions */
    public function __construct(
        private array $conditions,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/operations/v1/units-condition';
    }

    public function body(): array
    {
        return [
            'unitsConditions' => array_map(static fn (UnitConditionUpdate $c): array => $c->toArray(), $this->conditions),
        ];
    }
}
