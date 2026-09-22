<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetBlockRequest extends Request
{
    /** @param list<string> $expand */
    public function __construct(
        private string $blockId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/blocks/'.rawurlencode($this->blockId);
    }

    public function query(): array
    {
        return array_filter(['expand' => implode(',', $this->expand) ?: null], static fn (mixed $value): bool => $value !== null);
    }
}
