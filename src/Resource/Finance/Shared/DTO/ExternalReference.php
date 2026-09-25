<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** The payment service provider's references for a payment or refund. */
final readonly class ExternalReference
{
    public function __construct(
        public string $merchantReference,
        public string $pspReference,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            merchantReference: ResponseData::string($data, 'merchantReference'),
            pspReference: ResponseData::string($data, 'pspReference'),
        );
    }
}
