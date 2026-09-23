<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateAgeCategoryRequest extends Request
{
    public function __construct(
        private string $ageCategoryId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/age-categories/'.rawurlencode($this->ageCategoryId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
