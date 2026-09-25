<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\CreateUnit;

final readonly class BulkCreateUnitsRequest extends Request
{
    /**
     * @param list<CreateUnit> $units
     */
    public function __construct(
        private array $units,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units/bulk';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return [
            'units' => array_map(static fn (CreateUnit $unit): array => $unit->toArray(), $this->units),
        ];
    }
}
