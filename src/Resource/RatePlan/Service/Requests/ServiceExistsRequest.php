<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ServiceExistsRequest extends Request
{
    public function __construct(
        private string $serviceId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/services/'.rawurlencode($this->serviceId);
    }
}
