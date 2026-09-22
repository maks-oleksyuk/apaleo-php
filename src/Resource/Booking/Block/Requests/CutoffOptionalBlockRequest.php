<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Fired by apaleo's own scheduler when an optional block's cutoff is reached; exposed here mainly for completeness/testing. */
final readonly class CutoffOptionalBlockRequest extends Request
{
    public function __construct(
        private string $blockId,
        private \DateTimeImmutable $expectedOptionalCutoffUtc,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/block-actions/'.rawurlencode($this->blockId).'/cutoff-optional';
    }

    public function body(): array
    {
        return ['expectedOptionalCutoffUtc' => $this->expectedOptionalCutoffUtc->format(\DateTimeInterface::ATOM)];
    }
}
