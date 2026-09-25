<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CancellationFeeChange
{
    public function __construct(
        public ?\DateTimeImmutable $dueDateTime,
        public ?MonetaryValue $fee,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $fee = ResponseData::nested($data, 'fee');

        return new self(
            dueDateTime: ResponseData::nullableDateTime($data, 'dueDateTime'),
            fee: [] !== $fee ? MonetaryValue::fromArray($fee) : null,
        );
    }
}
