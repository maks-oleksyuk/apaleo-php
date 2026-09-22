<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** Why an {@see Action} is currently not allowed. $code is a raw API code (large, action-specific enum; not worth mirroring 1:1 here). */
final readonly class ActionReason
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
