<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateNoShowPolicyRequest extends Request
{
    public function __construct(
        private string $noShowPolicyId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/no-show-policies/'.rawurlencode($this->noShowPolicyId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
