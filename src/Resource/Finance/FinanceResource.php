<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Account\FinanceAccountResource;
use Oleksyuk\Apaleo\Resource\Finance\Folio\FolioResource;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\InvoiceResource;
use Oleksyuk\Apaleo\Resource\Finance\Payment\PaymentResource;
use Oleksyuk\Apaleo\Resource\Finance\Refund\RefundResource;
use Oleksyuk\Apaleo\Resource\Finance\Routing\RoutingResource;
use Oleksyuk\Apaleo\Resource\Finance\Types\FinanceTypesResource;

final readonly class FinanceResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function folios(): FolioResource
    {
        return new FolioResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function payments(): PaymentResource
    {
        return new PaymentResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function refunds(): RefundResource
    {
        return new RefundResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function invoices(): InvoiceResource
    {
        return new InvoiceResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function routings(): RoutingResource
    {
        return new RoutingResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function accounts(): FinanceAccountResource
    {
        return new FinanceAccountResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function types(): FinanceTypesResource
    {
        return new FinanceTypesResource($this->pipeline);
    }
}
