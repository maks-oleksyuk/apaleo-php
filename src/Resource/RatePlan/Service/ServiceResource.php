<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO\CreateService;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO\Service;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\CountServicesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\CreateServiceRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\DeleteServiceRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\GetServiceRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\ListServicesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\ServiceExistsRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests\UpdateServiceRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'property'> $expand
     * @param ?list<string> $languages
     */
    public function get(string $serviceId, array $expand = [], ?array $languages = null): Service
    {
        $data = $this->pipeline->send(new GetServiceRequest($serviceId, $expand, $languages));

        return Service::fromArray($data);
    }

    public function exists(string $serviceId): bool
    {
        try {
            $this->pipeline->send(new ServiceExistsRequest($serviceId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'property'> $expand
     *
     * @return PaginatedResult<Service>
     */
    public function list(
        ServiceFilter $filter = new ServiceFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListServicesRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Service::fromArray(...), ResponseData::nestedList($data, 'services')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(ServiceFilter $filter = new ServiceFilter()): int
    {
        $data = $this->pipeline->send(new CountServicesRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /** @return string the id of the created service */
    public function create(CreateService $data): string
    {
        $response = $this->pipeline->send(new CreateServiceRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $serviceId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateServiceRequest($serviceId, $patch));
    }

    public function delete(string $serviceId): void
    {
        $this->pipeline->send(new DeleteServiceRequest($serviceId));
    }
}
