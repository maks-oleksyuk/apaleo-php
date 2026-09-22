<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Reports\Enum\Gender;
use Oleksyuk\Apaleo\Resource\Reports\Enum\Title;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OrderedServiceGuest
{
    public function __construct(
        public ?Title $title,
        public ?Gender $gender,
        public ?string $firstName,
        public ?string $middleInitial,
        public string $lastName,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $title = ResponseData::nullableString($data, 'title');
        $gender = ResponseData::nullableString($data, 'gender');

        return new self(
            title: null !== $title ? Title::fromApi($title) : null,
            gender: null !== $gender ? Gender::fromApi($gender) : null,
            firstName: ResponseData::nullableString($data, 'firstName'),
            middleInitial: ResponseData::nullableString($data, 'middleInitial'),
            lastName: ResponseData::string($data, 'lastName'),
        );
    }
}
