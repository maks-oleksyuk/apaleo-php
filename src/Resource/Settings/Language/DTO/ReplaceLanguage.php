<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\Language\DTO;

final readonly class ReplaceLanguage
{
    public function __construct(
        public string $code,
        public bool $mandatory,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['code' => $this->code, 'mandatory' => $this->mandatory];
    }
}
