<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioItemSelection;

final readonly class MoveChargesRequest extends Request
{
    public function __construct(
        private string $folioId,
        private string $targetFolioId,
        private string $reason,
        private FolioItemSelection $items,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/move-charges';
    }

    public function body(): array
    {
        return ['targetFolioId' => $this->targetFolioId, 'reason' => $this->reason, ...$this->items->toArray()];
    }
}
