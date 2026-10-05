<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\ReservationsCreated;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\CreateGroup;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\Group;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\PickUpReservation;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\CountGroupsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\CreateGroupRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\DeleteGroupRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\GetGroupRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\GroupExistsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\ListGroupsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\PickUpReservationsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Group\Requests\UpdateGroupRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class GroupResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'actions'|'blocks'> $expand
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(string $groupId, array $expand = []): Group
    {
        $data = $this->pipeline->send(new GetGroupRequest($groupId, $expand));

        return Group::fromArray($data);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function exists(string $groupId): bool
    {
        try {
            $this->pipeline->send(new GroupExistsRequest($groupId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'actions'|'blocks'> $expand
     *
     * @return PaginatedResult<Group>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(
        GroupFilter $filter = new GroupFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListGroupsRequest($filter, $pageNumber, $pageSize, $expand));

        return PaginatedResult::fromResponse($data, 'groups', Group::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function count(GroupFilter $filter = new GroupFilter()): int
    {
        $data = $this->pipeline->send(new CountGroupsRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function create(CreateGroup $group, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreateGroupRequest($group, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function update(string $groupId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateGroupRequest($groupId, $patch));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function delete(string $groupId): void
    {
        $this->pipeline->send(new DeleteGroupRequest($groupId));
    }

    /**
     * @param list<PickUpReservation> $reservations
     *
     * @throws ApaleoExceptionInterface
     */
    public function pickUpReservations(string $groupId, array $reservations, ?string $idempotencyKey = null): ReservationsCreated
    {
        $data = $this->pipeline->send(new PickUpReservationsRequest($groupId, $reservations, $idempotencyKey));

        return ReservationsCreated::fromArray($data);
    }
}
