<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetServiceRequest extends Request
{
    /** @param list<'property'> $expand */
    public function __construct(
        private string $serviceId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/services/'.rawurlencode($this->serviceId);
    }

    /** Requests every configured language so localized fields are always a full map, never account-dependent. */
    public function query(): array
    {
        return array_filter([
            'languages' => 'all',
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
