<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Operations\MaintenanceFilter;

final readonly class CountMaintenancesRequest extends Request
{
    public function __construct(
        private MaintenanceFilter $filter = new MaintenanceFilter(),
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
