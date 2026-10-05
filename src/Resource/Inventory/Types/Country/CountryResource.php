<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Types\Country;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\Requests\ListCountriesRequest;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CountryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @return list<string> ISO Alpha-2 country codes supported by Apaleo
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(): array
    {
        $data = $this->pipeline->send(new ListCountriesRequest());

        return ResponseData::stringListOrEmpty($data, 'countryCodes');
    }
}
