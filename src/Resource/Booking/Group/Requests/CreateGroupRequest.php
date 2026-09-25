<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\CreateGroup;

final readonly class CreateGroupRequest extends Request
{
    public function __construct(
        private CreateGroup $group,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return $this->group->toArray();
    }
}
