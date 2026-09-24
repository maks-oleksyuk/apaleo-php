<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\CreateRatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\RatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\RatePlanListItem;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\ReplaceRatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\ArchiveRatePlanRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\BulkDeleteRatePlansRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\BulkUpdateRatePlansRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\CountRatePlansRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\CreateRatePlanRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\DeleteRatePlanRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\GetRatePlanRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\ListRatePlansRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\RatePlanExistsRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests\ReplaceRatePlanRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RatePlanDomainResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'cancellationPolicy'|'property'> $expand
     * @param ?list<string> $languages
     */
    public function get(string $ratePlanId, array $expand = [], ?array $languages = null): RatePlan
    {
        $data = $this->pipeline->send(new GetRatePlanRequest($ratePlanId, $expand, $languages));

        return RatePlan::fromArray($data);
    }

    public function exists(string $ratePlanId): bool
    {
        try {
            $this->pipeline->send(new RatePlanExistsRequest($ratePlanId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'ageCategories'|'bookingPeriods'|'cancellationPolicy'|'property'|'services'|'surcharges'|'unitGroup'> $expand
     *
     * @return PaginatedResult<RatePlanListItem>
     */
    public function list(
        RatePlanFilter $filter = new RatePlanFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListRatePlansRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(RatePlanListItem::fromArray(...), ResponseData::nestedList($data, 'ratePlans')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(RatePlanFilter $filter = new RatePlanFilter()): int
    {
        $data = $this->pipeline->send(new CountRatePlansRequest($filter));

        return ResponseData::int($data, 'count');
    }

    public function create(CreateRatePlan $data): string
    {
        $response = $this->pipeline->send(new CreateRatePlanRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function replace(string $ratePlanId, ReplaceRatePlan $data): void
    {
        $this->pipeline->send(new ReplaceRatePlanRequest($ratePlanId, $data));
    }

    /**
     * Apaleo has no single-rate-plan PATCH: pass one id to update just that one.
     *
     * @param list<string> $ratePlanIds
     */
    public function bulkUpdate(array $ratePlanIds, JsonPatch $patch): void
    {
        $this->pipeline->send(new BulkUpdateRatePlansRequest($ratePlanIds, $patch));
    }

    public function delete(string $ratePlanId): void
    {
        $this->pipeline->send(new DeleteRatePlanRequest($ratePlanId));
    }

    /** @param list<string> $ratePlanIds */
    public function bulkDelete(array $ratePlanIds): void
    {
        $this->pipeline->send(new BulkDeleteRatePlansRequest($ratePlanIds));
    }

    /** Makes the rate plan unavailable for all operations; cannot be undone. */
    public function archive(string $ratePlanId): void
    {
        $this->pipeline->send(new ArchiveRatePlanRequest($ratePlanId));
    }
}
