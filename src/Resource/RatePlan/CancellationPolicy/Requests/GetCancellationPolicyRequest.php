<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetCancellationPolicyRequest extends Request
{
    /** @param ?list<string> $languages */
    public function __construct(
        private string $cancellationPolicyId,
        private ?array $languages = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/cancellation-policies/'.rawurlencode($this->cancellationPolicyId);
    }

    public function query(): array
    {
        return array_filter([
            'languages' => $this->languages !== null ? implode(',', $this->languages) : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
