<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** The posted charge, plus one extra charge per fee the property has configured (e.g. a service fee). */
final readonly class AddedCharge
{
    /** @param list<string> $feeChargeIds */
    public function __construct(
        public string $id,
        public array $feeChargeIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            feeChargeIds: ResponseData::stringListOrEmpty($data, 'feeChargeIds'),
        );
    }
}
