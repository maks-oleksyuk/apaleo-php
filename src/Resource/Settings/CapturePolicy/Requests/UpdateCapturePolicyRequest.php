<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateCapturePolicyRequest extends Request
{
    public function __construct(
        private string $capturePolicyId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/capture-policies/'.rawurlencode($this->capturePolicyId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
