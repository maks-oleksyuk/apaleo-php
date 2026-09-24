<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListCapturePoliciesRequest extends Request
{
    public function __construct(
        private ?string $propertyId = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/capture-policies';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
