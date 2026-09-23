<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Operations\DTO\CreateMaintenance;
use Oleksyuk\Apaleo\Resource\Operations\DTO\Maintenance;
use Oleksyuk\Apaleo\Resource\Operations\DTO\UnitConditionUpdate;
use Oleksyuk\Apaleo\Resource\Operations\Requests\BulkCreateMaintenancesRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\CountMaintenancesRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\CreateMaintenanceRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\DeleteMaintenanceRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\GetMaintenanceRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\ListMaintenancesRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\MaintenanceExistsRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\PerformNightAuditRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\ReplaceUnitsConditionRequest;
use Oleksyuk\Apaleo\Resource\Operations\Requests\UpdateMaintenanceRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The Operations API (operations-v1) — maintenance windows plus two standalone actions (night audit, unit condition), so no sub-resources beneath this one. */
final readonly class OperationsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'unit'> $expand
     *
     * @return PaginatedResult<Maintenance>
     */
    public function listMaintenances(
        MaintenanceFilter $filter = new MaintenanceFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListMaintenancesRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Maintenance::fromArray(...), ResponseData::nestedList($data, 'maintenances')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function countMaintenances(MaintenanceFilter $filter = new MaintenanceFilter()): int
    {
        $data = $this->pipeline->send(new CountMaintenancesRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /** @param list<'unit'> $expand */
    public function getMaintenance(string $maintenanceId, array $expand = []): Maintenance
    {
        $data = $this->pipeline->send(new GetMaintenanceRequest($maintenanceId, $expand));

        return Maintenance::fromArray($data);
    }

    public function maintenanceExists(string $maintenanceId): bool
    {
        try {
            $this->pipeline->send(new MaintenanceExistsRequest($maintenanceId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /** @return string the id of the created maintenance */
    public function createMaintenance(CreateMaintenance $maintenance, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateMaintenanceRequest($maintenance, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    /**
     * @param list<CreateMaintenance> $maintenances
     *
     * @return list<string> the ids of the created maintenances
     */
    public function bulkCreateMaintenances(array $maintenances, ?string $idempotencyKey = null): array
    {
        $data = $this->pipeline->send(new BulkCreateMaintenancesRequest($maintenances, $idempotencyKey));

        return ResponseData::stringList($data, 'ids');
    }

    public function updateMaintenance(string $maintenanceId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateMaintenanceRequest($maintenanceId, $patch));
    }

    public function deleteMaintenance(string $maintenanceId): void
    {
        $this->pipeline->send(new DeleteMaintenanceRequest($maintenanceId));
    }

    /** Posts revenues for the past business day and sets all occupied units to 'Dirty'. */
    public function performNightAudit(string $propertyId, ?bool $setReservationsToNoShow = null): void
    {
        $this->pipeline->send(new PerformNightAuditRequest($propertyId, $setReservationsToNoShow));
    }

    /** @param list<UnitConditionUpdate> $conditions */
    public function setUnitsCondition(array $conditions): void
    {
        $this->pipeline->send(new ReplaceUnitsConditionRequest($conditions));
    }
}
