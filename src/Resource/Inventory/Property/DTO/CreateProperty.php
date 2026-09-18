<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\DTO;

/** Input for creating (or cloning-with-overrides) a property. */
final readonly class CreateProperty
{
    /**
     * @param array<string, string> $name localized, e.g. ['en' => 'Berlin Hotel']
     * @param null|array<string, string> $description
     * @param array<string, string> $paymentTerms localized
     */
    public function __construct(
        public string $code,
        public array $name,
        public string $companyName,
        public string $commercialRegisterEntry,
        public string $taxId,
        public Address $location,
        public array $paymentTerms,
        public string $timeZone,
        public string $defaultCheckInTime,
        public string $defaultCheckOutTime,
        public string $currencyCode,
        public ?string $managingDirectors = null,
        public ?array $description = null,
        public ?BankAccount $bankAccount = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'companyName' => $this->companyName,
            'managingDirectors' => $this->managingDirectors,
            'commercialRegisterEntry' => $this->commercialRegisterEntry,
            'taxId' => $this->taxId,
            'description' => $this->description,
            'location' => $this->location->toArray(),
            'bankAccount' => $this->bankAccount?->toArray(),
            'paymentTerms' => $this->paymentTerms,
            'timeZone' => $this->timeZone,
            'defaultCheckInTime' => $this->defaultCheckInTime,
            'defaultCheckOutTime' => $this->defaultCheckOutTime,
            'currencyCode' => $this->currencyCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
