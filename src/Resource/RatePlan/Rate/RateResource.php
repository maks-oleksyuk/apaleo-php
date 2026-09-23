<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\Rate;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\RatePatch;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\ReplaceRate;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\BulkUpdateRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\CountRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\DeleteRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\ListRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\ReplaceRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests\UpdateRatesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\DayOfWeek;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * Per-time-slice prices and restrictions of a rate plan. $from/$to are sent as dates
 * (Y-m-d, property-local): a rate is included when its own `from` falls in [$from, $to).
 */
final readonly class RateResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return PaginatedResult<Rate> */
    public function list(
        string $ratePlanId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListRatesRequest($ratePlanId, $from, $to, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(Rate::fromArray(...), ResponseData::nestedList($data, 'rates')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(string $ratePlanId, \DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        $data = $this->pipeline->send(new CountRatesRequest($ratePlanId, $from, $to));

        return ResponseData::int($data, 'count');
    }

    /** @param list<ReplaceRate> $rates */
    public function replace(string $ratePlanId, array $rates): void
    {
        $this->pipeline->send(new ReplaceRatesRequest($ratePlanId, $rates));
    }

    /** @param list<RatePatch> $patches */
    public function update(string $ratePlanId, array $patches): void
    {
        $this->pipeline->send(new UpdateRatesRequest($ratePlanId, $patches));
    }

    /**
     * Applies one patch to the rates of several rate plans at once.
     *
     * @param list<string> $ratePlanIds
     * @param list<DayOfWeek> $weekDays empty = every day
     */
    public function bulkUpdate(
        array $ratePlanIds,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        JsonPatch $patch,
        array $weekDays = [],
    ): void {
        $this->pipeline->send(new BulkUpdateRatesRequest($ratePlanIds, $from, $to, $patch, $weekDays));
    }

    /** Deletes every rate in the range. */
    public function delete(string $ratePlanId, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $this->pipeline->send(new DeleteRatesRequest($ratePlanId, $from, $to));
    }
}
