<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetAgeCategoryRequest extends Request
{
    public function __construct(
        private string $ageCategoryId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/age-categories/'.rawurlencode($this->ageCategoryId);
    }

    /** Requests every configured language so localized fields are always a full map, never account-dependent. */
    public function query(): array
    {
        return ['languages' => 'all'];
    }
}
