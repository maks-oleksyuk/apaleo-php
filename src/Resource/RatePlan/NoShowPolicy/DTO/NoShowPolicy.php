<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class NoShowPolicy
{
    /**
     * @param array<string, string> $name localized, or ['default' => ...] when the endpoint returns a plain string
     * @param array<string, string> $description
     */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public FeeDetails $fee,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            fee: FeeDetails::fromArray(ResponseData::nested($data, 'fee')),
        );
    }
}
