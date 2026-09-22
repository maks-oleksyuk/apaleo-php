<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Shared shape for the block-actions endpoints that take no body: cancel, confirm, release, wash. */
final readonly class BlockSimpleActionRequest extends Request
{
    public function __construct(
        private string $blockId,
        private string $action,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/block-actions/'.rawurlencode($this->blockId).'/'.$this->action;
    }
}
