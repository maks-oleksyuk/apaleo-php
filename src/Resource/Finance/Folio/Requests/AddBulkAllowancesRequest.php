<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkAllowanceItem;

final readonly class AddBulkAllowancesRequest extends Request
{
    /** @param list<BulkAllowanceItem> $items */
    public function __construct(
        private string $folioId,
        private array $items,
        private string $reason,
        private ?\DateTimeImmutable $businessDate = null,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/bulk-allowances';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return array_filter([
            'items' => array_map(static fn (BulkAllowanceItem $i): array => $i->toArray(), $this->items),
            'reason' => $this->reason,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
