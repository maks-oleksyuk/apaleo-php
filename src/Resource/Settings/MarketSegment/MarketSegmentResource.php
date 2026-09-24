<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO\CreateMarketSegment;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO\MarketSegment;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\CountMarketSegmentsRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\CreateMarketSegmentRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\DeleteMarketSegmentRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\GetMarketSegmentRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\ListMarketSegmentsRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\MarketSegmentExistsRequest;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\Requests\UpdateMarketSegmentRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class MarketSegmentResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $marketSegmentId): MarketSegment
    {
        $data = $this->pipeline->send(new GetMarketSegmentRequest($marketSegmentId));

        return MarketSegment::fromArray($data);
    }

    public function exists(string $marketSegmentId): bool
    {
        try {
            $this->pipeline->send(new MarketSegmentExistsRequest($marketSegmentId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<string> $propertyIds
     *
     * @return PaginatedResult<MarketSegment>
     */
    public function list(array $propertyIds = [], ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListMarketSegmentsRequest($propertyIds, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(MarketSegment::fromArray(...), ResponseData::nestedList($data, 'marketSegments')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** @param list<string> $propertyIds */
    public function count(array $propertyIds = []): int
    {
        $data = $this->pipeline->send(new CountMarketSegmentsRequest($propertyIds));

        return ResponseData::int($data, 'count');
    }

    /** @return string the id of the created market segment */
    public function create(CreateMarketSegment $data): string
    {
        $response = $this->pipeline->send(new CreateMarketSegmentRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $marketSegmentId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateMarketSegmentRequest($marketSegmentId, $patch));
    }

    public function delete(string $marketSegmentId): void
    {
        $this->pipeline->send(new DeleteMarketSegmentRequest($marketSegmentId));
    }
}
