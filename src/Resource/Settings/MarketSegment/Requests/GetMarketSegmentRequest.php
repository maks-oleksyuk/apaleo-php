<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetMarketSegmentRequest extends Request
{
    public function __construct(
        private string $marketSegmentId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/market-segments/'.rawurlencode($this->marketSegmentId);
    }
}
