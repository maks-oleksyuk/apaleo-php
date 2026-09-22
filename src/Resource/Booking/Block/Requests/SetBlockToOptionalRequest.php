<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\OptionalCutoffBehavior;

final readonly class SetBlockToOptionalRequest extends Request
{
    public function __construct(
        private string $blockId,
        private string $optionalCutoff,
        private bool $isOptionalDeductingInventory,
        private OptionalCutoffBehavior $optionalCutoffBehavior,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/block-actions/'.rawurlencode($this->blockId).'/set-to-optional';
    }

    public function body(): array
    {
        return [
            'optionalCutoff' => $this->optionalCutoff,
            'isOptionalDeductingInventory' => $this->isOptionalDeductingInventory,
            'optionalCutoffBehavior' => $this->optionalCutoffBehavior->value,
        ];
    }
}
