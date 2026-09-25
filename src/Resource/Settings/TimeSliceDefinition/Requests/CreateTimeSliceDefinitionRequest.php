<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO\CreateTimeSliceDefinition;

final readonly class CreateTimeSliceDefinitionRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private CreateTimeSliceDefinition $data,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/settings/v1/properties/'.rawurlencode($this->propertyId).'/time-slice-definitions';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
