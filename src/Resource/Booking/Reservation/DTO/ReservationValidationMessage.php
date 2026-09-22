<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** category/code are raw API values (large enums; informational, not branched on). */
final readonly class ReservationValidationMessage
{
    public function __construct(
        public string $category,
        public string $code,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            category: ResponseData::string($data, 'category'),
            code: ResponseData::string($data, 'code'),
            message: ResponseData::string($data, 'message'),
        );
    }
}
