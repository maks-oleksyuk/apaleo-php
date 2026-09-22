<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class UpdateBlockRequest extends Request
{
    public function __construct(
        private readonly string $blockId,
        private readonly JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/booking/v1/blocks/'.rawurlencode($this->blockId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
