<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

/** Which charges, allowances and transitory charges a move or a folio correction should take. */
final readonly class FolioItemSelection
{
    /**
     * @param list<string> $chargeIds
     * @param list<string> $allowanceIds
     * @param list<string> $transitoryChargeIds
     */
    public function __construct(
        public array $chargeIds = [],
        public array $allowanceIds = [],
        public array $transitoryChargeIds = [],
    ) {}

    /** @return array<string, list<string>> */
    public function toArray(): array
    {
        return array_filter([
            'chargeIds' => $this->chargeIds,
            'allowanceIds' => $this->allowanceIds,
            'transitoryChargeIds' => $this->transitoryChargeIds,
        ]);
    }
}
