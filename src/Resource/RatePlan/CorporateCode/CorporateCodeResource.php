<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode\DTO\CorporateCode;
use Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode\Requests\ListCorporateCodesRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CorporateCodeResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return PaginatedResult<CorporateCode> */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListCorporateCodesRequest($propertyId, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(CorporateCode::fromArray(...), ResponseData::nestedList($data, 'corporateCodes')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }
}
