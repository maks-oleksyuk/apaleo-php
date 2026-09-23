<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteCancellationPolicyRequest extends Request
{
    public function __construct(
        private string $cancellationPolicyId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/cancellation-policies/'.rawurlencode($this->cancellationPolicyId);
    }
}
