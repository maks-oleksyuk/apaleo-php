<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

/**
 * A list() response together with the total count Apaleo already includes in the same body —
 * no separate count() request needed to know if there's more to page through.
 * Behaves like the plain list<T> it replaces: countable, iterable, and index-readable.
 *
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 * @implements \ArrayAccess<int, T>
 */
final readonly class PaginatedResult implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @param list<T> $items this page's items
     */
    public function __construct(
        public array $items,
        public int $totalCount,
    ) {}

    /** Count of items on this page (not $totalCount — use that for the overall total). */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @return T
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException(self::class.' is read-only.');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException(self::class.' is read-only.');
    }
}
