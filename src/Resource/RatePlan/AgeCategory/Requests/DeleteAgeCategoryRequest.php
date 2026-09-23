<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteAgeCategoryRequest extends Request
{
    public function __construct(
        private string $ageCategoryId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/settings/v1/age-categories/'.rawurlencode($this->ageCategoryId);
    }
}
