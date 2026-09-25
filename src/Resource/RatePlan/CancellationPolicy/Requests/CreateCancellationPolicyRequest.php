<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CreateCancellationPolicy;

final readonly class CreateCancellationPolicyRequest extends Request
{
    public function __construct(
        private CreateCancellationPolicy $data,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/cancellation-policies';
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
