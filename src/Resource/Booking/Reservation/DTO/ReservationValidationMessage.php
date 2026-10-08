<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationValidationMessageCategory;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationValidationMessageCode;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationValidationMessage
{
    public function __construct(
        public ReservationValidationMessageCategory $category,
        public ReservationValidationMessageCode $code,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            category: ReservationValidationMessageCategory::fromApi(ResponseData::string($data, 'category')),
            code: ReservationValidationMessageCode::fromApi(ResponseData::string($data, 'code')),
            message: ResponseData::string($data, 'message'),
        );
    }
}
