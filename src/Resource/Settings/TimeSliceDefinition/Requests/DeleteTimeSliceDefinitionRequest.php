<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteTimeSliceDefinitionRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private string $timeSliceDefinitionId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/settings/v1/properties/'.rawurlencode($this->propertyId).'/time-slice-definitions/'.rawurlencode($this->timeSliceDefinitionId);
    }
}
