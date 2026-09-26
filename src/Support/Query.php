<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

final class Query
{
    /**
     * A list query parameter as Apaleo expects it: comma-separated, enums by value, and null
     * (omitted) only when the list is empty, so a lone 0 (an infant's age) is still sent.
     *
     * @param list<\BackedEnum|float|int|string> $values
     */
    public static function csv(array $values): ?string
    {
        if ($values === []) {
            return null;
        }

        return implode(',', array_map(static fn (\BackedEnum|float|int|string $value): string => (string) ($value instanceof \BackedEnum ? $value->value : $value), $values));
    }
}
