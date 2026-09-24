<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;

final readonly class ListPaymentsRequest extends Request
{
    /**
     * @param list<PaymentStatus> $statuses
     * @param list<'actions'> $expand
     */
    public function __construct(
        private string $folioId,
        private array $statuses = [],
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folios/'.rawurlencode($this->folioId).'/payments';
    }

    public function query(): array
    {
        return array_filter([
            'statusCodes' => implode(',', array_map(static fn (PaymentStatus $s): string => $s->value, $this->statuses)) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
