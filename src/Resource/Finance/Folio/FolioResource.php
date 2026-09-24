<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\AddedCharge;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkAllowanceItem;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkMoveItem;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateCharge;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateFolio;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateFolioAllowance;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateTransitoryCharge;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\Folio;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioItemSelection;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioListItem;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\Split;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\SplitChargeResult;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddBulkAllowancesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddChargeAllowanceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddChargeRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddFeeRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddFolioAllowanceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\AddTransitoryChargeRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\BulkMoveChargesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\CorrectFolioRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\CountFoliosRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\CreateFolioRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\DeleteFolioRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\FolioExistsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\FolioSimpleActionRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\GetFolioRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\ListFoliosRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\MoveAllChargesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\MoveChargesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\MovePaymentsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\SplitChargeRequest;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Requests\UpdateFolioRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * Folios and the folio-actions on them. Every create-style method accepts an $idempotencyKey:
 * pass one so a retried request isn't posted twice.
 */
final readonly class FolioResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'folios'> $expand */
    public function get(string $folioId, array $expand = []): Folio
    {
        $data = $this->pipeline->send(new GetFolioRequest($folioId, $expand));

        return Folio::fromArray($data);
    }

    public function exists(string $folioId): bool
    {
        try {
            $this->pipeline->send(new FolioExistsRequest($folioId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'balance:asc'|'balance:desc'|'created:asc'|'created:desc'>                                    $sort
     * @param list<'allowances'|'allowedActions'|'charges'|'company'|'payments'|'transitoryCharges'|'warnings'> $expand
     *
     * @return PaginatedResult<FolioListItem>
     */
    public function list(
        FolioFilter $filter = new FolioFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $sort = [],
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListFoliosRequest($filter, $pageNumber, $pageSize, $sort, $expand));

        return new PaginatedResult(
            items: array_map(FolioListItem::fromArray(...), ResponseData::nestedList($data, 'folios')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(FolioFilter $filter = new FolioFilter()): int
    {
        $data = $this->pipeline->send(new CountFoliosRequest($filter));

        return ResponseData::int($data, 'count');
    }

    public function create(CreateFolio $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateFolioRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    public function update(string $folioId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateFolioRequest($folioId, $patch));
    }

    public function delete(string $folioId): void
    {
        $this->pipeline->send(new DeleteFolioRequest($folioId));
    }

    public function close(string $folioId): void
    {
        $this->pipeline->send(new FolioSimpleActionRequest($folioId, 'close'));
    }

    public function reopen(string $folioId): void
    {
        $this->pipeline->send(new FolioSimpleActionRequest($folioId, 'reopen'));
    }

    /** Posts every not-yet-posted charge for the whole stay, instead of night by night. */
    public function postCharges(string $folioId): void
    {
        $this->pipeline->send(new FolioSimpleActionRequest($folioId, 'post-charges'));
    }

    public function addCharge(string $folioId, CreateCharge $charge, ?string $idempotencyKey = null): AddedCharge
    {
        $data = $this->pipeline->send(new AddChargeRequest($folioId, $charge, $idempotencyKey));

        return AddedCharge::fromArray($data);
    }

    public function addTransitoryCharge(string $folioId, CreateTransitoryCharge $charge, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new AddTransitoryChargeRequest($folioId, $charge, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /** Routed to the routing's destination folio if a routing covers cancellation fees. */
    public function addCancellationFee(string $folioId, MonetaryValue $amount, ?string $idempotencyKey = null): AddedCharge
    {
        $data = $this->pipeline->send(new AddFeeRequest($folioId, 'cancellation-fee', $amount, $idempotencyKey));

        return AddedCharge::fromArray($data);
    }

    /** Routed to the routing's destination folio if a routing covers no-show fees. */
    public function addNoShowFee(string $folioId, MonetaryValue $amount, ?string $idempotencyKey = null): AddedCharge
    {
        $data = $this->pipeline->send(new AddFeeRequest($folioId, 'no-show-fee', $amount, $idempotencyKey));

        return AddedCharge::fromArray($data);
    }

    public function addChargeAllowance(
        string $folioId,
        string $chargeId,
        string $reason,
        MonetaryValue $amount,
        ?\DateTimeImmutable $businessDate = null,
        ?string $idempotencyKey = null,
    ): string {
        $data = $this->pipeline->send(new AddChargeAllowanceRequest($folioId, $chargeId, $reason, $amount, $businessDate, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function addFolioAllowance(string $folioId, CreateFolioAllowance $allowance, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new AddFolioAllowanceRequest($folioId, $allowance, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /**
     * @param list<BulkAllowanceItem> $items
     *
     * @return array<string, string> the created allowance ids, keyed by the charge id each was granted on
     */
    public function addBulkAllowances(
        string $folioId,
        array $items,
        string $reason,
        ?\DateTimeImmutable $businessDate = null,
        ?string $idempotencyKey = null,
    ): array {
        $data = $this->pipeline->send(new AddBulkAllowancesRequest($folioId, $items, $reason, $businessDate, $idempotencyKey));

        $allowanceIds = [];
        foreach (ResponseData::nestedList($data, 'items') as $item) {
            $allowanceIds[ResponseData::string($item, 'sourceChargeId')] = ResponseData::string($item, 'id');
        }

        return $allowanceIds;
    }

    public function moveCharges(string $folioId, string $targetFolioId, string $reason, FolioItemSelection $items): void
    {
        $this->pipeline->send(new MoveChargesRequest($folioId, $targetFolioId, $reason, $items));
    }

    /** Moves all charges and transitory charges (not allowances or payments). */
    public function moveAllCharges(string $folioId, string $targetFolioId, string $reason): void
    {
        $this->pipeline->send(new MoveAllChargesRequest($folioId, $targetFolioId, $reason));
    }

    /** @param list<BulkMoveItem> $items */
    public function bulkMoveCharges(array $items, string $reason): void
    {
        $this->pipeline->send(new BulkMoveChargesRequest($items, $reason));
    }

    /**
     * Only between guest and booking folios.
     *
     * @param list<string> $paymentIds
     */
    public function movePayments(string $folioId, string $targetFolioId, string $reason, array $paymentIds): void
    {
        $this->pipeline->send(new MovePaymentsRequest($folioId, $targetFolioId, $reason, $paymentIds));
    }

    /**
     * Moves the given items to a new folio, together with a matching share of the payments, so both folios end at a zero balance.
     *
     * @return string the id of the new folio
     */
    public function correct(string $folioId, string $reason, FolioItemSelection $items, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CorrectFolioRequest($folioId, $reason, $items, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function splitCharge(string $folioId, string $chargeId, Split $split, ?string $idempotencyKey = null): SplitChargeResult
    {
        $data = $this->pipeline->send(new SplitChargeRequest($folioId, $chargeId, $split, $idempotencyKey));

        return SplitChargeResult::fromArray($data);
    }
}
