<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListInvoiceAddressesRequest extends Request
{
    /** @param list<string> $propertyIds */
    public function __construct(
        private array $propertyIds = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/invoice-address';
    }

    public function query(): array
    {
        return array_filter([
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
