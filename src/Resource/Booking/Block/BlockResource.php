<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\Block;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\CreateBlock;
use Oleksyuk\Apaleo\Resource\Booking\Block\DTO\ReplaceBlock;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\OptionalCutoffBehavior;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\AmendBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\BlockExistsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\BlockSimpleActionRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\CountBlocksRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\CreateBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\CutoffOptionalBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\DeleteBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\GetBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\ListBlocksRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\SetBlockToOptionalRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\StartOptionalBlockRequest;
use Oleksyuk\Apaleo\Resource\Booking\Block\Requests\UpdateBlockRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class BlockResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'|'timeSlices'> $expand */
    public function get(string $blockId, array $expand = []): Block
    {
        $data = $this->pipeline->send(new GetBlockRequest($blockId, $expand));

        return Block::fromArray($data);
    }

    public function exists(string $blockId): bool
    {
        try {
            $this->pipeline->send(new BlockExistsRequest($blockId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'actions'|'timeSlices'> $expand
     *
     * @return PaginatedResult<Block>
     */
    public function list(
        BlockFilter $filter = new BlockFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListBlocksRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Block::fromArray(...), ResponseData::nestedList($data, 'blocks')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(BlockFilter $filter = new BlockFilter()): int
    {
        $data = $this->pipeline->send(new CountBlocksRequest($filter));

        return ResponseData::int($data, 'count');
    }

    public function create(CreateBlock $block, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreateBlockRequest($block, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function update(string $blockId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateBlockRequest($blockId, $patch));
    }

    public function delete(string $blockId): void
    {
        $this->pipeline->send(new DeleteBlockRequest($blockId));
    }

    public function amend(string $blockId, ReplaceBlock $replacement): void
    {
        $this->pipeline->send(new AmendBlockRequest($blockId, $replacement));
    }

    public function cancel(string $blockId): void
    {
        $this->pipeline->send(new BlockSimpleActionRequest($blockId, 'cancel'));
    }

    public function confirm(string $blockId): void
    {
        $this->pipeline->send(new BlockSimpleActionRequest($blockId, 'confirm'));
    }

    public function release(string $blockId): void
    {
        $this->pipeline->send(new BlockSimpleActionRequest($blockId, 'release'));
    }

    /** Picks up (deletes) any of the block's unpicked units that are no longer needed. */
    public function wash(string $blockId): void
    {
        $this->pipeline->send(new BlockSimpleActionRequest($blockId, 'wash'));
    }

    public function setToOptional(
        string $blockId,
        string $optionalCutoff,
        bool $isOptionalDeductingInventory,
        OptionalCutoffBehavior $optionalCutoffBehavior,
    ): void {
        $this->pipeline->send(new SetBlockToOptionalRequest($blockId, $optionalCutoff, $isOptionalDeductingInventory, $optionalCutoffBehavior));
    }

    /** For apaleo's own scheduler callbacks; see {@see CutoffOptionalBlockRequest}. */
    public function cutoffOptional(string $blockId, \DateTimeImmutable $expectedOptionalCutoffUtc): void
    {
        $this->pipeline->send(new CutoffOptionalBlockRequest($blockId, $expectedOptionalCutoffUtc));
    }

    /** For apaleo's own scheduler callbacks; see {@see StartOptionalBlockRequest}. */
    public function startOptional(string $blockId, \DateTimeImmutable $expectedStartDateUtc): void
    {
        $this->pipeline->send(new StartOptionalBlockRequest($blockId, $expectedStartDateUtc));
    }
}
