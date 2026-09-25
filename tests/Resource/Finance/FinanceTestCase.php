<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Oleksyuk\Apaleo\Resource\Finance\FinanceResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\TestCase;

abstract class FinanceTestCase extends TestCase
{
    use MockPipeline;

    protected FinanceResource $api;

    protected function setUp(): void
    {
        $this->api = new FinanceResource($this->createPipeline());
    }
}
