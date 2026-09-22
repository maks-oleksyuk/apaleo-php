<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group;

/** Filter criteria shared by GroupResource::list() and ::count(). */
final readonly class GroupFilter
{
    /** @param list<string> $propertyIds */
    public function __construct(
        public ?string $textSearch = null,
        public array $propertyIds = [],
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        public ?bool $hasActivePaymentAccount = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'textSearch' => $this->textSearch,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'hasActivePaymentAccount' => $this->hasActivePaymentAccount,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
