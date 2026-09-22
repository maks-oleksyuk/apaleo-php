<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** code is a raw API value (large enum; informational, not branched on). */
final readonly class OfferValidationMessage
{
    public function __construct(
        public string $code,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            message: ResponseData::string($data, 'message'),
        );
    }
}
