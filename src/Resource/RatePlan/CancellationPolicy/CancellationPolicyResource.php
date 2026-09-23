<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CancellationPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CreateCancellationPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests\CreateCancellationPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests\DeleteCancellationPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests\GetCancellationPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests\ListCancellationPoliciesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests\UpdateCancellationPolicyRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CancellationPolicyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $cancellationPolicyId): CancellationPolicy
    {
        $data = $this->pipeline->send(new GetCancellationPolicyRequest($cancellationPolicyId));

        return CancellationPolicy::fromArray($data);
    }

    /** @return PaginatedResult<CancellationPolicy> */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListCancellationPoliciesRequest($propertyId, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(CancellationPolicy::fromArray(...), ResponseData::nestedList($data, 'cancellationPolicies')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** @return string the id of the created cancellation policy */
    public function create(CreateCancellationPolicy $data): string
    {
        $response = $this->pipeline->send(new CreateCancellationPolicyRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $cancellationPolicyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateCancellationPolicyRequest($cancellationPolicyId, $patch));
    }

    public function delete(string $cancellationPolicyId): void
    {
        $this->pipeline->send(new DeleteCancellationPolicyRequest($cancellationPolicyId));
    }
}
