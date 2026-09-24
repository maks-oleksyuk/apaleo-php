<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListTimeSliceDefinitionsRequest extends Request
{
    /** @param list<'actions'> $expand */
    public function __construct(
        private string $propertyId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/properties/'.rawurlencode($this->propertyId).'/time-slice-definitions';
    }

    public function query(): array
    {
        return array_filter([
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
