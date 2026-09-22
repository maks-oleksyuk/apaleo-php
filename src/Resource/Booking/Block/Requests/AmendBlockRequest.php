<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\ReplaceBlock;

final readonly class AmendBlockRequest extends Request
{
    public function __construct(
        private string $blockId,
        private ReplaceBlock $replacement,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/block-actions/'.rawurlencode($this->blockId).'/amend';
    }

    public function body(): array
    {
        return $this->replacement->toArray();
    }
}
