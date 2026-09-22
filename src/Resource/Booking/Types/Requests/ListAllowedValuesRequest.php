<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Types\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Types\Enum\AllowedValueType;

final class ListAllowedValuesRequest extends Request
{
    public function __construct(
        private readonly AllowedValueType $type,
        private readonly string $countryCode,
        private readonly ?string $textSearch = null,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/types/'.rawurlencode($this->type->value).'/allowed-values';
    }

    public function query(): array
    {
        return array_filter([
            'countryCode' => $this->countryCode,
            'textSearch' => $this->textSearch,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
