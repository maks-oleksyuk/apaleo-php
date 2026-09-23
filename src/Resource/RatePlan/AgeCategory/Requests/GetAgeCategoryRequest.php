<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetAgeCategoryRequest extends Request
{
    /** @param ?list<string> $languages */
    public function __construct(
        private string $ageCategoryId,
        private ?array $languages = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/age-categories/'.rawurlencode($this->ageCategoryId);
    }

    public function query(): array
    {
        return array_filter([
            'languages' => $this->languages !== null ? implode(',', $this->languages) : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
