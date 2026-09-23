<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Company\Enum\InvoiceNetwork;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The company's id on an e-invoicing network, e.g. a Peppol participant id. */
final readonly class InvoiceNetworkIdentity
{
    public function __construct(
        public InvoiceNetwork $network,
        public string $id,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            network: InvoiceNetwork::fromApi(ResponseData::string($data, 'network')),
            id: ResponseData::string($data, 'id'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['network' => $this->network->value, 'id' => $this->id];
    }
}
