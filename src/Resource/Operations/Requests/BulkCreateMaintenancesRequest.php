<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Operations\DTO\CreateMaintenance;

final readonly class BulkCreateMaintenancesRequest extends Request
{
    /**
     * @param list<CreateMaintenance> $maintenances
     * @param ?string $idempotencyKey lets a retried request be recognized and not create duplicate maintenances
     */
    public function __construct(
        private array $maintenances,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances/bulk';
    }

    public function body(): array
    {
        return [
            'maintenances' => array_map(static fn (CreateMaintenance $m): array => $m->toArray(), $this->maintenances),
        ];
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }
}
