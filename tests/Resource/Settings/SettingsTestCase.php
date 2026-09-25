<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Settings;

use Oleksyuk\Apaleo\Resource\Settings\SettingsResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\TestCase;

abstract class SettingsTestCase extends TestCase
{
    use MockPipeline;

    protected SettingsResource $api;

    protected function setUp(): void
    {
        $this->api = new SettingsResource($this->createPipeline());
    }
}
