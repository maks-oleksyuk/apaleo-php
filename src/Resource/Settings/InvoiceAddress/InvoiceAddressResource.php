<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO\InvoiceAddress;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO\ReplaceInvoiceAddress;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests\ListInvoiceAddressesRequest;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests\ReplaceInvoiceAddressRequest;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\Requests\UpdateInvoiceAddressRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The address printed on invoices, when it differs from the property's own. One address per property. */
final readonly class InvoiceAddressResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string> $propertyIds empty for all properties
     *
     * @return PaginatedResult<InvoiceAddress>
     */
    public function list(array $propertyIds = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListInvoiceAddressesRequest($propertyIds));

        return new PaginatedResult(
            items: array_map(InvoiceAddress::fromArray(...), ResponseData::nestedList($data, 'addresses')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /**
     * Creates or replaces the invoice address of every given property.
     *
     * @param non-empty-list<string> $propertyIds
     */
    public function replace(array $propertyIds, ReplaceInvoiceAddress $data): void
    {
        $this->pipeline->send(new ReplaceInvoiceAddressRequest($propertyIds, $data));
    }

    /** @param non-empty-list<string> $propertyIds */
    public function update(array $propertyIds, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateInvoiceAddressRequest($propertyIds, $patch));
    }
}
