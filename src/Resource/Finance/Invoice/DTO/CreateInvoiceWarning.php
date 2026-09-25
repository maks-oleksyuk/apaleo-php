<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\CreateInvoiceWarningType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Why creating the invoice would fail or need attention, as reported by the preview. */
final readonly class CreateInvoiceWarning
{
    public function __construct(
        public CreateInvoiceWarningType $type,
        public ?string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: CreateInvoiceWarningType::fromApi(ResponseData::string($data, 'type')),
            message: ResponseData::nullableString($data, 'message'),
        );
    }
}
