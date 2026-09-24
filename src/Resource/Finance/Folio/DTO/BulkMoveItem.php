<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

final readonly class BulkMoveItem
{
    /** @param list<string> $chargeIds */
    public function __construct(
        public string $sourceFolioId,
        public string $targetFolioId,
        public array $chargeIds = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'sourceFolioId' => $this->sourceFolioId,
            'targetFolioId' => $this->targetFolioId,
            'chargeIds' => $this->chargeIds ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
