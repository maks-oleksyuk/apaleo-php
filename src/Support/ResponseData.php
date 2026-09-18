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

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return \is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return (\is_int($value) || \is_string($value)) ? (int) $value : null;
    }

    /**
     * Extracts a nested object field as an array; missing or malformed becomes [].
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function nested(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return \is_array($value) ? self::onlyStringKeys($value) : [];
    }

    /**
     * Extracts a list field as an array of arrays; missing, malformed or non-array items are dropped.
     *
     * @param array<string, mixed> $data
     *
     * @return list<array<string, mixed>>
     */
    public static function nestedList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (!\is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (\is_array($item)) {
                $items[] = self::onlyStringKeys($item);
            }
        }

        return $items;
    }

    /**
     * Apaleo returns some text fields as a plain string, others as a language-keyed map
     * (e.g. {"en": "..."}) depending on the endpoint. Normalizes both into a map.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    public static function localizedText(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (\is_string($value)) {
            return ['default' => $value];
        }

        if (!\is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $lang => $text) {
            if (\is_string($lang) && \is_string($text)) {
                $result[$lang] = $text;
            }
        }

        return $result;
    }

    /**
     * @param array<mixed, mixed> $data
     *
     * @return array<string, mixed>
     */
    private static function onlyStringKeys(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (\is_string($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
