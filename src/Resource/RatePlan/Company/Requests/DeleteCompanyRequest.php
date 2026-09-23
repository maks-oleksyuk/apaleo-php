<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteCompanyRequest extends Request
{
    public function __construct(
        private string $companyId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/companies/'.rawurlencode($this->companyId);
    }
}
