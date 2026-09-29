<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\PromoCode;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\PromoCode\DTO\PromoCode;
use Oleksyuk\Apaleo\Resource\RatePlan\PromoCode\Requests\ListPromoCodesRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;

final readonly class PromoCodeResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return PaginatedResult<PromoCode> */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListPromoCodesRequest($propertyId, $pageNumber, $pageSize));

        return PaginatedResult::fromResponse($data, 'promoCodes', PromoCode::fromArray(...));
    }
}
