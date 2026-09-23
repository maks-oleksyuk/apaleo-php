<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetCancellationPolicyRequest extends Request
{
    public function __construct(
        private string $cancellationPolicyId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/cancellation-policies/'.rawurlencode($this->cancellationPolicyId);
    }

    /** Requests every configured language so localized fields are always a full map, never account-dependent. */
    public function query(): array
    {
        return ['languages' => 'all'];
    }
}
