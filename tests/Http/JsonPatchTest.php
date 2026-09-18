<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Oleksyuk\Apaleo\Http\JsonPatch;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class JsonPatchTest extends TestCase
{
    public function testBuildsOperationsInOrder(): void
    {
        $patch = new JsonPatch()
            ->replace('/name', 'New Name')
            ->add('/attributes/-', ['id' => 'A1'])
            ->remove('/description')
        ;

        self::assertSame([
            ['op' => 'replace', 'path' => '/name', 'value' => 'New Name'],
            ['op' => 'add', 'path' => '/attributes/-', 'value' => ['id' => 'A1']],
            ['op' => 'remove', 'path' => '/description'],
        ], $patch->toArray());
    }

    public function testEmptyPatchProducesEmptyArray(): void
    {
        self::assertSame([], new JsonPatch()->toArray());
    }
}
