<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Offer\Enum\OfferValidationMessageCode;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OfferValidationMessage
{
    public function __construct(
        public OfferValidationMessageCode $code,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: OfferValidationMessageCode::fromApi(ResponseData::string($data, 'code')),
            message: ResponseData::string($data, 'message'),
        );
    }
}
