<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Routing\DTO\CreateRouting;
use Oleksyuk\Apaleo\Resource\Finance\Routing\DTO\Routing;
use Oleksyuk\Apaleo\Resource\Finance\Routing\Requests\CreateRoutingRequest;
use Oleksyuk\Apaleo\Resource\Finance\Routing\Requests\DeleteRoutingRequest;
use Oleksyuk\Apaleo\Resource\Finance\Routing\Requests\GetRoutingRequest;
use Oleksyuk\Apaleo\Resource\Finance\Routing\Requests\ListRoutingsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Routing\Requests\UpdateRoutingRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Rules that automatically move a booking's matching charges to another folio as they get posted. */
final readonly class RoutingResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'> $expand */
    public function get(string $routingId, array $expand = []): Routing
    {
        $data = $this->pipeline->send(new GetRoutingRequest($routingId, $expand));

        return Routing::fromArray($data);
    }

    /**
     * @param list<'actions'> $expand
     *
     * @return PaginatedResult<Routing>
     */
    public function list(RoutingFilter $filter = new RoutingFilter(), ?int $pageNumber = null, ?int $pageSize = null, array $expand = []): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListRoutingsRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Routing::fromArray(...), ResponseData::nestedList($data, 'routings')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** @return string the id of the created routing */
    public function create(CreateRouting $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateRoutingRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    public function update(string $routingId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateRoutingRequest($routingId, $patch));
    }

    public function delete(string $routingId): void
    {
        $this->pipeline->send(new DeleteRoutingRequest($routingId));
    }
}
