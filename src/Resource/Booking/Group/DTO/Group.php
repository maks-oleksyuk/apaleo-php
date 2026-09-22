<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Group
{
    /**
     * @param list<string>     $propertyIds
     * @param list<GroupBlock> $blocks
     * @param list<Action>     $actions
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?\DateTimeImmutable $from,
        public ?\DateTimeImmutable $to,
        public ?Booker $booker,
        public ?string $comment,
        public ?string $bookerComment,
        public bool $hasActivePaymentAccount,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $modified,
        public array $blocks,
        public array $actions,
        public array $propertyIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $booker = ResponseData::nested($data, 'booker');

        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            from: ResponseData::nullableDateTime($data, 'from'),
            to: ResponseData::nullableDateTime($data, 'to'),
            booker: $booker !== [] ? Booker::fromArray($booker) : null,
            comment: ResponseData::nullableString($data, 'comment'),
            bookerComment: ResponseData::nullableString($data, 'bookerComment'),
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            blocks: array_map(GroupBlock::fromArray(...), ResponseData::nestedList($data, 'blocks')),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
            propertyIds: ResponseData::stringList($data, 'propertyIds'),
        );
    }
}
