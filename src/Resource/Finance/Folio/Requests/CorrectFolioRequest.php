<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioItemSelection;

final readonly class CorrectFolioRequest extends Request
{
    public function __construct(
        private string $folioId,
        private string $reason,
        private FolioItemSelection $items,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/correct';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return ['reason' => $this->reason, ...$this->items->toArray()];
    }
}
