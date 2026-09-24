<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\Language\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\Language\DTO\ReplaceLanguage;

final readonly class ReplaceLanguagesRequest extends Request
{
    /** @param list<ReplaceLanguage> $languages */
    public function __construct(
        private array $languages,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/settings/v1/languages';
    }

    public function body(): array
    {
        return ['languages' => array_map(static fn (ReplaceLanguage $l): array => $l->toArray(), $this->languages)];
    }
}
