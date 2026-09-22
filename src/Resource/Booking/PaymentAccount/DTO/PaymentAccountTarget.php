<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** What a payment account is for: a whole booking, or a single reservation within one. Same shape as AuthorizationTarget, minus propertyId. */
final readonly class PaymentAccountTarget
{
    public function __construct(
        public AuthorizationTargetType $type,
        public string $id,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: AuthorizationTargetType::fromApi(ResponseData::string($data, 'type')),
            id: ResponseData::string($data, 'id'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['type' => $this->type->value, 'id' => $this->id];
    }
}
