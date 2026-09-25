<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource;

use Oleksyuk\Apaleo\Resource\Availability\AvailabilityResource;
use Oleksyuk\Apaleo\Resource\Booking\BookingResource;
use Oleksyuk\Apaleo\Resource\Inventory\InventoryResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AvailabilityResource::class)]
#[CoversClass(BookingResource::class)]
#[CoversClass(InventoryResource::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class ResourceAccessorsTest extends TestCase
{
    use MockPipeline;

    /** @param class-string $aggregate */
    #[DataProvider('provideEveryAccessorReturnsItsSubResourceCases')]
    public function testEveryAccessorReturnsItsSubResource(string $aggregate): void
    {
        $resource = new $aggregate($this->createPipeline());

        $accessors = array_filter(
            new \ReflectionClass($resource)->getMethods(\ReflectionMethod::IS_PUBLIC),
            static fn (\ReflectionMethod $method): bool => !$method->isConstructor(),
        );
        self::assertNotEmpty($accessors);

        foreach ($accessors as $accessor) {
            $returnType = $accessor->getReturnType();
            self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
            $subResource = $returnType->getName();
            \assert(class_exists($subResource));
            self::assertInstanceOf($subResource, $accessor->invoke($resource), $accessor->getName());
        }
    }

    /** @return iterable<string, array{class-string}> */
    public static function provideEveryAccessorReturnsItsSubResourceCases(): iterable
    {
        yield 'availability' => [AvailabilityResource::class];

        yield 'booking' => [BookingResource::class];

        yield 'inventory' => [InventoryResource::class];
    }
}
