<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\CreateNoShowPolicy;

final readonly class CreateNoShowPolicyRequest extends Request
{
    public function __construct(
        private CreateNoShowPolicy $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/no-show-policies';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
