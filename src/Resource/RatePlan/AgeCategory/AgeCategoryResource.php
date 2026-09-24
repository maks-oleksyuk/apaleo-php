<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO\AgeCategory;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO\AgeCategoryListItem;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO\CreateAgeCategory;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests\CreateAgeCategoryRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests\DeleteAgeCategoryRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests\GetAgeCategoryRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests\ListAgeCategoriesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\Requests\UpdateAgeCategoryRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AgeCategoryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param ?list<string> $languages */
    public function get(string $ageCategoryId, ?array $languages = null): AgeCategory
    {
        $data = $this->pipeline->send(new GetAgeCategoryRequest($ageCategoryId, $languages));

        return AgeCategory::fromArray($data);
    }

    /** @return PaginatedResult<AgeCategoryListItem> */
    public function list(string $propertyId): PaginatedResult
    {
        $data = $this->pipeline->send(new ListAgeCategoriesRequest($propertyId));

        return new PaginatedResult(
            items: array_map(AgeCategoryListItem::fromArray(...), ResponseData::nestedList($data, 'ageCategories')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(CreateAgeCategory $data): string
    {
        $response = $this->pipeline->send(new CreateAgeCategoryRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $ageCategoryId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateAgeCategoryRequest($ageCategoryId, $patch));
    }

    public function delete(string $ageCategoryId): void
    {
        $this->pipeline->send(new DeleteAgeCategoryRequest($ageCategoryId));
    }
}
