<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CapturePolicy;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\DTO\CapturePolicy;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\DTO\CapturePolicyListItem;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Requests\GetCapturePolicyRequest;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Requests\ListCapturePoliciesRequest;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Requests\UpdateCapturePolicyRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;

final readonly class CapturePolicyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $capturePolicyId): CapturePolicy
    {
        $data = $this->pipeline->send(new GetCapturePolicyRequest($capturePolicyId));

        return CapturePolicy::fromArray($data);
    }

    /** @return PaginatedResult<CapturePolicyListItem> */
    public function list(?string $propertyId = null, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListCapturePoliciesRequest($propertyId, $pageNumber, $pageSize));

        return PaginatedResult::fromResponse($data, 'capturePolicies', CapturePolicyListItem::fromArray(...));
    }

    public function update(string $capturePolicyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateCapturePolicyRequest($capturePolicyId, $patch));
    }
}
