<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateCompanyRequest extends Request
{
    public function __construct(
        private string $companyId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/companies/'.rawurlencode($this->companyId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
