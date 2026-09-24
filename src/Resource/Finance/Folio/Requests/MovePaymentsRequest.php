<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class MovePaymentsRequest extends Request
{
    /** @param list<string> $paymentIds */
    public function __construct(
        private string $folioId,
        private string $targetFolioId,
        private string $reason,
        private array $paymentIds,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/move-payments';
    }

    public function body(): array
    {
        return ['targetFolioId' => $this->targetFolioId, 'reason' => $this->reason, 'paymentIds' => $this->paymentIds];
    }
}
