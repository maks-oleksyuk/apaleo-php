<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\Language\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** An account language; $mandatory ones must be filled in on every localized field. */
final readonly class Language
{
    public function __construct(
        public string $code,
        public bool $default,
        public bool $mandatory,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            default: ResponseData::bool($data, 'default'),
            mandatory: ResponseData::bool($data, 'mandatory'),
        );
    }
}
