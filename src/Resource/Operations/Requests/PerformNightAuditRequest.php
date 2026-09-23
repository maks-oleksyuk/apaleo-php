<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class PerformNightAuditRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private ?bool $setReservationsToNoShow = null,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/operations/v1/night-audit';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'setReservationsToNoShow' => $this->setReservationsToNoShow,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
