<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Refund\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\CreateFolioRefund;

final readonly class CreateFolioRefundRequest extends Request
{
    public function __construct(
        private string $folioId,
        private CreateFolioRefund $data,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId).'/refunds';
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
