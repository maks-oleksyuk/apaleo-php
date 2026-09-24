<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** The legal details of the issuing company printed on the invoice. */
final readonly class CommercialInfo
{
    public function __construct(
        public string $registerEntry,
        public string $taxId,
        public ?string $managingDirectors,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            registerEntry: ResponseData::string($data, 'registerEntry'),
            taxId: ResponseData::string($data, 'taxId'),
            managingDirectors: ResponseData::nullableString($data, 'managingDirectors'),
        );
    }
}
