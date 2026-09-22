<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class DeleteBlockRequest extends Request
{
    public function __construct(
        private readonly string $blockId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/booking/v1/blocks/'.rawurlencode($this->blockId);
    }
}
