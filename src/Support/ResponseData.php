<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

/** Strict field extraction from decoded JSON API responses (untrusted, typed as mixed). */
final class ResponseData
{
    /**
     * @param array<string, mixed> $data
     */
    public static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value)) {
            throw new \UnexpectedValueException("Expected string for field \"{$key}\" in Apaleo API response.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        if (!\is_int($value) && !\is_string($value)) {
            throw new \UnexpectedValueException("Expected int for field \"{$key}\" in Apaleo API response.");
        }

        return (int) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function bool(array $data, string $key, bool $default = false): bool
    {
        $value = $data[$key] ?? $default;

        return \is_bool($value) ? $value : $default;
    }
}
