<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Operations\DTO\CreateMaintenance;

final readonly class CreateMaintenanceRequest extends Request
{
    /** @param ?string $idempotencyKey lets a retried request be recognized and not create a duplicate maintenance */
    public function __construct(
        private CreateMaintenance $maintenance,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances';
    }

    public function body(): array
    {
        return $this->maintenance->toArray();
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }
}
