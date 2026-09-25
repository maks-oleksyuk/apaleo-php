<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO\CreateMarketSegment;

final readonly class CreateMarketSegmentRequest extends Request
{
    public function __construct(
        private CreateMarketSegment $data,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/settings/v1/market-segments';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
