<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;

/**
 * Strict field extraction from decoded JSON API responses (untrusted, typed as mixed).
 * Strings are trimmed: many fields are typed in by hotel staff and carry stray whitespace.
 */
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

        return mb_trim($value);
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
    public static function float(array $data, string $key): float
    {
        $value = $data[$key] ?? null;
        if (!\is_int($value) && !\is_float($value)) {
            throw new ApaleoUnexpectedResponseException("Expected number for field \"{$key}\" in Apaleo API response.");
        }

        return (float) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        return \is_int($value) || \is_float($value) ? (float) $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function dateTime(array $data, string $key): \DateTimeImmutable
    {
        return self::parseDateTime($key, self::string($data, $key));
    }

    /**
     * For `format: date` fields ("2026-09-22"): midnight UTC, so the value doesn't depend on
     * date.timezone and a round trip through format('Y-m-d') gives back the same day.
     *
     * @param array<string, mixed> $data
     */
    public static function date(array $data, string $key): \DateTimeImmutable
    {
        return self::parseDate($key, self::string($data, $key));
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableDate(array $data, string $key): ?\DateTimeImmutable
    {
        $value = $data[$key] ?? null;

        return \is_string($value) ? self::parseDate($key, $value) : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableDateTime(array $data, string $key): ?\DateTimeImmutable
    {
        $value = self::nullableString($data, $key);

        return $value !== null ? self::parseDateTime($key, $value) : null;
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

        if (!\is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value !== '' ? $value : null;
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
     * Tolerant int list: missing/null/non-array becomes [], non-int items are dropped.
     *
     * @param array<string, mixed> $data
     *
     * @return list<int>
     */
    public static function intList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return \is_array($value) ? array_values(array_filter($value, \is_int(...))) : [];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    public static function stringList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (!\is_array($value)) {
            throw new ApaleoUnexpectedResponseException("Expected list for field \"{$key}\" in Apaleo API response.");
        }

        return array_map(mb_trim(...), array_values(array_filter($value, \is_string(...))));
    }

    /**
     * Tolerant string list: missing/null/non-array becomes [], non-string items are dropped.
     * Use for a field apaleo omits entirely rather than sending an empty array.
     *
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    public static function stringListOrEmpty(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return \is_array($value) ? array_map(mb_trim(...), array_values(array_filter($value, \is_string(...)))) : [];
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
     * Maps an optional nested object: missing, null or empty becomes null instead of a DTO
     * built from nothing.
     *
     * @template T
     *
     * @param array<string, mixed>                $data
     * @param callable(array<string, mixed>): T $map e.g. Address::fromArray(...)
     *
     * @return null|T
     */
    public static function nullableNested(array $data, string $key, callable $map): mixed
    {
        $value = self::nested($data, $key);

        return $value !== [] ? $map($value) : null;
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
            return ['default' => mb_trim($value)];
        }

        if (!\is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $lang => $text) {
            if (\is_string($lang) && \is_string($text)) {
                $result[$lang] = mb_trim($text);
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

    /**
     * ISO 8601 only: the DateTimeImmutable constructor alone would also accept "" (now) or "tomorrow".
     */
    private static function parseDateTime(string $key, string $value): \DateTimeImmutable
    {
        $message = "Invalid date-time for field \"{$key}\" in Apaleo API response: \"{$value}\".";
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value) !== 1) {
            throw new ApaleoUnexpectedResponseException($message);
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new ApaleoUnexpectedResponseException($message, 0, previous: $exception);
        }
    }

    private static function parseDate(string $key, string $value): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('UTC'));
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new ApaleoUnexpectedResponseException("Invalid date for field \"{$key}\" in Apaleo API response: \"{$value}\".");
        }

        return $date;
    }
}
