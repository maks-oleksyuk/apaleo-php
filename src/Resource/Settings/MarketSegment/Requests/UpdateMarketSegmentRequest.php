<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateMarketSegmentRequest extends Request
{
    public function __construct(
        private string $marketSegmentId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/market-segments/'.rawurlencode($this->marketSegmentId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
