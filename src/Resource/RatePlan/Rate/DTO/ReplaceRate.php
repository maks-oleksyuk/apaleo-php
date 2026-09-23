<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;

/** One time slice for PUT /rate-plans/{id}/rates; $from/$to must match an existing slice exactly. */
final readonly class ReplaceRate
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public ?MonetaryValue $price = null,
        public ?RateRestrictions $restrictions = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from->format(\DateTimeInterface::ATOM),
            'to' => $this->to->format(\DateTimeInterface::ATOM),
            'price' => $this->price?->toArray(),
            'restrictions' => $this->restrictions?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
