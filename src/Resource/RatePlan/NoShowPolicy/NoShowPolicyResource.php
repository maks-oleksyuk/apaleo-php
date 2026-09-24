<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\CreateNoShowPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\NoShowPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\NoShowPolicyListItem;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests\CreateNoShowPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests\DeleteNoShowPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests\GetNoShowPolicyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests\ListNoShowPoliciesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\Requests\UpdateNoShowPolicyRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class NoShowPolicyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param ?list<string> $languages */
    public function get(string $noShowPolicyId, ?array $languages = null): NoShowPolicy
    {
        $data = $this->pipeline->send(new GetNoShowPolicyRequest($noShowPolicyId, $languages));

        return NoShowPolicy::fromArray($data);
    }

    /** @return PaginatedResult<NoShowPolicyListItem> */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListNoShowPoliciesRequest($propertyId, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(NoShowPolicyListItem::fromArray(...), ResponseData::nestedList($data, 'noShowPolicies')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(CreateNoShowPolicy $data): string
    {
        $response = $this->pipeline->send(new CreateNoShowPolicyRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $noShowPolicyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateNoShowPolicyRequest($noShowPolicyId, $patch));
    }

    public function delete(string $noShowPolicyId): void
    {
        $this->pipeline->send(new DeleteNoShowPolicyRequest($noShowPolicyId));
    }
}
