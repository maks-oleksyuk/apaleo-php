<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListExternalAccountsRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private string $folioId,
        private ?string $parent = null,
        private ?string $languageCode = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/external-accounts';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'folioId' => $this->folioId,
            'parent' => $this->parent,
            'languageCode' => $this->languageCode,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
