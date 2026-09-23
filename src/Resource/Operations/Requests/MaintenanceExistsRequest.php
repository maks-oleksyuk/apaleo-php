<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class MaintenanceExistsRequest extends Request
{
    public function __construct(
        private string $maintenanceId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances/'.rawurlencode($this->maintenanceId);
    }
}
