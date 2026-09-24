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
    public static function fromNested(array $data, string $key): ?self
    {
        $warning = ResponseData::nested($data, $key);

        return $warning !== [] ? new self(
            type: CreateInvoiceWarningType::fromApi(ResponseData::string($warning, 'type')),
            message: ResponseData::nullableString($warning, 'message'),
        ) : null;
    }
}
