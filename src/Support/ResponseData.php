<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;

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
            throw new ApaleoUnexpectedResponseException("Expected string for field \"{$key}\" in Apaleo API response.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        $filtered = \is_int($value) || \is_string($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($filtered === false) {
            throw new ApaleoUnexpectedResponseException("Expected int for field \"{$key}\" in Apaleo API response.");
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function dateTime(array $data, string $key): \DateTimeImmutable
    {
        $value = self::string($data, $key);

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new ApaleoUnexpectedResponseException("Invalid date-time for field \"{$key}\" in Apaleo API response: \"{$value}\".", $exception->getCode(), previous: $exception);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableDateTime(array $data, string $key): ?\DateTimeImmutable
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new ApaleoUnexpectedResponseException("Invalid date-time for field \"{$key}\" in Apaleo API response: \"{$value}\".", $exception->getCode(), previous: $exception);
        }
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
        if ($value === null) {
            return null;
        }

        $filtered = \is_int($value) || \is_string($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($filtered === false) {
            throw new ApaleoUnexpectedResponseException("Expected int for field \"{$key}\" in Apaleo API response.");
        }

        return $filtered;
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
     * Apaleo's single-GET endpoints (property/unit/unit-group) return a language-keyed map
     * (e.g. {"en": "...", "de": "..."}) and respect `?languages=`, which the corresponding
     * Get*Request classes pin to "all" for a deterministic full map. List endpoints always
     * return a plain string instead, regardless of `languages` — verified live, not
     * configurable — so those are normalized here into a single-entry {"default": ...} map.
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
