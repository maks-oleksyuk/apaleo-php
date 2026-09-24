<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO\ReplaceInvoiceAddress;

final readonly class ReplaceInvoiceAddressRequest extends Request
{
    /** @param list<string> $propertyIds */
    public function __construct(
        private array $propertyIds,
        private ReplaceInvoiceAddress $data,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/settings/v1/invoice-address';
    }

    public function query(): array
    {
        return ['propertyIds' => implode(',', $this->propertyIds)];
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
