<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PaymentAccountLink
{
    public function __construct(
        public ?\DateTimeImmutable $expiresAt,
        public ?string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            expiresAt: ResponseData::nullableDateTime($data, 'expiresAt'),
            url: ResponseData::nullableString($data, 'url'),
        );
    }
}
