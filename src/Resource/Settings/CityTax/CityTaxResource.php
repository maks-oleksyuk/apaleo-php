<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CityTax;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CityTaxListItem;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CreateCityTax;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests\CreateCityTaxRequest;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests\DeleteCityTaxRequest;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests\GetCityTaxRequest;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests\ListCityTaxesRequest;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests\UpdateCityTaxRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CityTaxResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param ?list<string> $languages
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(string $cityTaxId, ?array $languages = null): CityTax
    {
        $data = $this->pipeline->send(new GetCityTaxRequest($cityTaxId, $languages));

        return CityTax::fromArray($data);
    }

    /**
     * @return PaginatedResult<CityTaxListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(?string $propertyId = null): PaginatedResult
    {
        $data = $this->pipeline->send(new ListCityTaxesRequest($propertyId));

        return PaginatedResult::fromResponse($data, 'cityTaxes', CityTaxListItem::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function create(CreateCityTax $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateCityTaxRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function update(string $cityTaxId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateCityTaxRequest($cityTaxId, $patch));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function delete(string $cityTaxId): void
    {
        $this->pipeline->send(new DeleteCityTaxRequest($cityTaxId));
    }
}
