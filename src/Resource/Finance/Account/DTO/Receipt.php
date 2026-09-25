<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\ReceiptType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The document a transaction is backed by, e.g. an invoice number or a PSP reference. */
final readonly class Receipt
{
    public function __construct(
        public string $number,
        public ?ReceiptType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::nullableString($data, 'type');

        return new self(
            number: ResponseData::string($data, 'number'),
            type: $type !== null ? ReceiptType::fromApi($type) : null,
        );
    }
}
