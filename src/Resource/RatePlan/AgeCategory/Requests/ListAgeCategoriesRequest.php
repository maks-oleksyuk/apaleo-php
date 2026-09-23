<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListAgeCategoriesRequest extends Request
{
    public function __construct(
        private string $propertyId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/age-categories';
    }

    public function query(): array
    {
        return ['propertyId' => $this->propertyId];
    }
}
