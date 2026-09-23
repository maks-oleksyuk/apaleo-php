<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateMaintenanceRequest extends Request
{
    public function __construct(
        private string $maintenanceId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/operations/v1/maintenances/'.rawurlencode($this->maintenanceId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
