<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;

final class ListPropertiesRequest extends Request
{
    /**
     * @param list<PropertyStatus> $status
     * @param list<string> $countryCode ISO Alpha-2 country codes
     * @param list<string> $expand
     */
    public function __construct(
        private readonly array $status = [],
        private readonly ?bool $includeArchived = null,
        private readonly array $countryCode = [],
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
        private readonly array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/properties';
    }

    public function query(): array
    {
        return array_filter([
            'status' => implode(',', array_map(
                static fn (PropertyStatus $s): string => $s->value,
                array_filter($this->status, static fn (PropertyStatus $s): bool => $s !== PropertyStatus::Unknown),
            )) ?: null,
            'includeArchived' => $this->includeArchived,
            'countryCode' => implode(',', $this->countryCode) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
