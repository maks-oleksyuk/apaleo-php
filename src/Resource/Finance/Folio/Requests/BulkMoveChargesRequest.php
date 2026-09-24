<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkMoveItem;

final readonly class BulkMoveChargesRequest extends Request
{
    /** @param list<BulkMoveItem> $items */
    public function __construct(
        private array $items,
        private string $reason,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/bulk-move';
    }

    public function body(): array
    {
        return ['items' => array_map(static fn (BulkMoveItem $i): array => $i->toArray(), $this->items), 'reason' => $this->reason];
    }
}
