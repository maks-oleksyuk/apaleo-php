<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Types\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Types\Enum\AllowedValueType;

final readonly class ListAllowedValuesRequest extends Request
{
    public function __construct(
        private AllowedValueType $type,
        private string $countryCode,
        private ?string $textSearch = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
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
