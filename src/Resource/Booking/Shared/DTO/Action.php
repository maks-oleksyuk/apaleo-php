<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * Whether a given action (e.g. "CheckIn", "Cancel") is currently allowed on the resource it's
 * attached to, and why not. $action is a raw API value (large, resource-specific enum; not
 * worth mirroring 1:1 here since it's informational, not something callers branch on).
 */
final readonly class Action
{
    /** @param list<ActionReason> $reasons */
    public function __construct(
        public string $action,
        public bool $isAllowed,
        public array $reasons,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            action: ResponseData::string($data, 'action'),
            isAllowed: ResponseData::bool($data, 'isAllowed'),
            reasons: array_map(ActionReason::fromArray(...), ResponseData::nestedList($data, 'reasons')),
        );
    }
}
