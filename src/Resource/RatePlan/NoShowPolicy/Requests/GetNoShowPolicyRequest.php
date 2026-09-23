<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetNoShowPolicyRequest extends Request
{
    public function __construct(
        private string $noShowPolicyId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/no-show-policies/'.rawurlencode($this->noShowPolicyId);
    }

    /** Requests every configured language so localized fields are always a full map, never account-dependent. */
    public function query(): array
    {
        return ['languages' => 'all'];
    }
}
