<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateInvoiceAddressRequest extends Request
{
    /** @param list<string> $propertyIds */
    public function __construct(
        private array $propertyIds,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
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
        return $this->patch->toArray();
    }
}
