<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlanResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\TestCase;

abstract class RatePlanTestCase extends TestCase
{
    use MockPipeline;

    protected RatePlanResource $api;

    protected function setUp(): void
    {
        $this->api = new RatePlanResource($this->createPipeline());
    }
}
