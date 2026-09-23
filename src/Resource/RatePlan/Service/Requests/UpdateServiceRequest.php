<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateServiceRequest extends Request
{
    public function __construct(
        private string $serviceId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/services/'.rawurlencode($this->serviceId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
