<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO\CreateTimeSliceDefinition;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO\TimeSliceDefinition;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests\CreateTimeSliceDefinitionRequest;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests\DeleteTimeSliceDefinitionRequest;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests\GetTimeSliceDefinitionRequest;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests\ListTimeSliceDefinitionsRequest;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\Requests\UpdateTimeSliceDefinitionRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TimeSliceDefinitionResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'> $expand */
    public function get(string $propertyId, string $timeSliceDefinitionId, array $expand = []): TimeSliceDefinition
    {
        $data = $this->pipeline->send(new GetTimeSliceDefinitionRequest($propertyId, $timeSliceDefinitionId, $expand));

        return TimeSliceDefinition::fromArray($data);
    }

    /**
     * @param list<'actions'> $expand
     *
     * @return PaginatedResult<TimeSliceDefinition>
     */
    public function list(string $propertyId, array $expand = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListTimeSliceDefinitionsRequest($propertyId, $expand));

        return new PaginatedResult(
            items: array_map(TimeSliceDefinition::fromArray(...), ResponseData::nestedList($data, 'timeSliceDefinitions')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** @return string the id of the created time slice definition */
    public function create(string $propertyId, CreateTimeSliceDefinition $data): string
    {
        $response = $this->pipeline->send(new CreateTimeSliceDefinitionRequest($propertyId, $data));

        return ResponseData::string($response, 'id');
    }

    public function update(string $propertyId, string $timeSliceDefinitionId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateTimeSliceDefinitionRequest($propertyId, $timeSliceDefinitionId, $patch));
    }

    public function delete(string $propertyId, string $timeSliceDefinitionId): void
    {
        $this->pipeline->send(new DeleteTimeSliceDefinitionRequest($propertyId, $timeSliceDefinitionId));
    }
}
