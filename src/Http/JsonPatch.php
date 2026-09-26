<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http;

/**
 * Values may be enums (sent by value) and dates (sent as ISO 8601 date-times).
 * For a date-only field, pass the 'Y-m-d' string instead.
 */
final class JsonPatch
{
    /** @var list<array<string, mixed>> */
    private array $operations = [];

    public function add(string $path, mixed $value): static
    {
        $this->operations[] = ['op' => 'add', 'path' => $path, 'value' => $value];

        return $this;
    }

    public function replace(string $path, mixed $value): static
    {
        $this->operations[] = ['op' => 'replace', 'path' => $path, 'value' => $value];

        return $this;
    }

    public function remove(string $path): static
    {
        $this->operations[] = ['op' => 'remove', 'path' => $path];

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->operations;
    }
}
