<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Types;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Types\Enum\AllowedValueType;
use Oleksyuk\Apaleo\Resource\Booking\Types\Requests\ListAllowedValuesRequest;
use Oleksyuk\Apaleo\Resource\Booking\Types\Requests\ListSourcesRequest;

final readonly class TypesResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return list<string> channels usable as a booking source */
    public function sources(): array
    {
        $data = $this->pipeline->send(new ListSourcesRequest());

        return $this->stringListOrEmpty($data, 'sources');
    }

    /**
     * @return list<string> values a field of $type can take in $countryCode; empty when the API
     *                       returns 204 No Content (no matching values on that page)
     */
    public function allowedValues(
        AllowedValueType $type,
        string $countryCode,
        ?string $textSearch = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): array {
        $data = $this->pipeline->send(new ListAllowedValuesRequest($type, $countryCode, $textSearch, $pageNumber, $pageSize));

        return $this->stringListOrEmpty($data, 'allowedValues');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    private function stringListOrEmpty(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return \is_array($value) ? array_values(array_filter($value, \is_string(...))) : [];
    }
}
