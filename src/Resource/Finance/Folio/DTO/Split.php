<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

/** How to split a charge or a payment in two: the first part gets the given share, the second the rest. */
final readonly class Split
{
    private function __construct(
        public ?float $percent,
        public ?MonetaryValue $amount,
    ) {}

    public static function byPercent(float $percent): self
    {
        return new self($percent, null);
    }

    public static function byAmount(MonetaryValue $amount): self
    {
        return new self(null, $amount);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->amount instanceof MonetaryValue
            ? ['type' => 'ByAmount', 'amount' => $this->amount->toArray()]
            : ['type' => 'ByPercent', 'percent' => $this->percent];
    }
}
