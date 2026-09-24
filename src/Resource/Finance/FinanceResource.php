<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Account\FinanceAccountResource;
use Oleksyuk\Apaleo\Resource\Finance\Folio\FolioResource;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\InvoiceResource;
use Oleksyuk\Apaleo\Resource\Finance\Payment\PaymentResource;
use Oleksyuk\Apaleo\Resource\Finance\Refund\RefundResource;
use Oleksyuk\Apaleo\Resource\Finance\Routing\RoutingResource;
use Oleksyuk\Apaleo\Resource\Finance\Types\FinanceTypesResource;

/** Aggregates the Finance API's sub-resources. */
final readonly class FinanceResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function folios(): FolioResource
    {
        return new FolioResource($this->pipeline);
    }

    public function payments(): PaymentResource
    {
        return new PaymentResource($this->pipeline);
    }

    public function refunds(): RefundResource
    {
        return new RefundResource($this->pipeline);
    }

    public function invoices(): InvoiceResource
    {
        return new InvoiceResource($this->pipeline);
    }

    public function routings(): RoutingResource
    {
        return new RoutingResource($this->pipeline);
    }

    public function accounts(): FinanceAccountResource
    {
        return new FinanceAccountResource($this->pipeline);
    }

    public function types(): FinanceTypesResource
    {
        return new FinanceTypesResource($this->pipeline);
    }
}
