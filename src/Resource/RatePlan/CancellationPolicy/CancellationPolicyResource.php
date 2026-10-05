<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CancellationPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CancellationPolicyListItem;
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

    /**
     * @param ?list<string> $languages
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(string $cancellationPolicyId, ?array $languages = null): CancellationPolicy
    {
        $data = $this->pipeline->send(new GetCancellationPolicyRequest($cancellationPolicyId, $languages));

        return CancellationPolicy::fromArray($data);
    }

    /**
     * @return PaginatedResult<CancellationPolicyListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListCancellationPoliciesRequest($propertyId, $pageNumber, $pageSize));

        return PaginatedResult::fromResponse($data, 'cancellationPolicies', CancellationPolicyListItem::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function create(CreateCancellationPolicy $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateCancellationPolicyRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function update(string $cancellationPolicyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateCancellationPolicyRequest($cancellationPolicyId, $patch));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function delete(string $cancellationPolicyId): void
    {
        $this->pipeline->send(new DeleteCancellationPolicyRequest($cancellationPolicyId));
    }
}
