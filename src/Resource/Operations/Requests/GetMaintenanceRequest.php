<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetMaintenanceRequest extends Request
{
    /** @param list<'unit'> $expand */
    public function __construct(
        private string $maintenanceId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances/'.rawurlencode($this->maintenanceId);
    }

    public function query(): array
    {
        return array_filter([
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
