<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Types\Country;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\Requests\ListCountriesRequest;

final readonly class CountryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @return list<string> ISO Alpha-2 country codes supported by Apaleo
     */
    public function list(): array
    {
        $data = $this->pipeline->send(new ListCountriesRequest());
        $codes = $data['countryCodes'] ?? null;
        if (!\is_array($codes)) {
            return [];
        }

        $result = [];
        foreach ($codes as $code) {
            if (\is_string($code)) {
                $result[] = $code;
            }
        }

        return $result;
    }
}
