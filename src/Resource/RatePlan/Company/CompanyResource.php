<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\Company;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\CreateCompany;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests\CreateCompanyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests\DeleteCompanyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests\GetCompanyRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests\ListCompaniesRequest;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests\UpdateCompanyRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CompanyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $companyId): Company
    {
        $data = $this->pipeline->send(new GetCompanyRequest($companyId));

        return Company::fromArray($data);
    }

    /** @return PaginatedResult<Company> */
    public function list(
        CompanyFilter $filter = new CompanyFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListCompaniesRequest($filter, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(Company::fromArray(...), ResponseData::nestedList($data, 'companies')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(CreateCompany $data): string
    {
        $response = $this->pipeline->send(new CreateCompanyRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $companyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateCompanyRequest($companyId, $patch));
    }

    public function delete(string $companyId): void
    {
        $this->pipeline->send(new DeleteCompanyRequest($companyId));
    }
}
