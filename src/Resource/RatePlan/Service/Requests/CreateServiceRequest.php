<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO\CreateService;

final readonly class CreateServiceRequest extends Request
{
    public function __construct(
        private CreateService $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/services';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
