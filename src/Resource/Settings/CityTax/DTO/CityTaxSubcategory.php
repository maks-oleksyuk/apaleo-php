<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A reduced city tax for an age group; $minAge and $maxAge are both inclusive. */
final readonly class CityTaxSubcategory
{
    /** @param array<string, string> $name localized */
    public function __construct(
        public array $name,
        public float $value,
        public int $minAge,
        public int $maxAge,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $age = ResponseData::nested($data, 'age');

        return new self(
            name: ResponseData::localizedText($data, 'name'),
            value: ResponseData::float($data, 'value'),
            minAge: ResponseData::int($age, 'min'),
            maxAge: ResponseData::int($age, 'max'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'age' => ['min' => $this->minAge, 'max' => $this->maxAge],
        ];
    }
}
