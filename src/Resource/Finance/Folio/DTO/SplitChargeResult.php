<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A split voids the original charge with an allowance and posts two new charges in its place. */
final readonly class SplitChargeResult
{
    public function __construct(
        public string $allowanceId,
        public string $firstChargeId,
        public string $secondChargeId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            allowanceId: ResponseData::string($data, 'allowanceId'),
            firstChargeId: ResponseData::string($data, 'firstChargeId'),
            secondChargeId: ResponseData::string($data, 'secondChargeId'),
        );
    }
}
