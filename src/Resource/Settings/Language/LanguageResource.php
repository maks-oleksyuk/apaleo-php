<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\Language;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\Language\DTO\Language;
use Oleksyuk\Apaleo\Resource\Settings\Language\DTO\ReplaceLanguage;
use Oleksyuk\Apaleo\Resource\Settings\Language\Requests\GetLanguagesRequest;
use Oleksyuk\Apaleo\Resource\Settings\Language\Requests\ReplaceLanguagesRequest;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The account's languages, i.e. which keys localized fields accept. */
final readonly class LanguageResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return list<Language> */
    public function list(): array
    {
        $data = $this->pipeline->send(new GetLanguagesRequest());

        return array_map(Language::fromArray(...), ResponseData::nestedList($data, 'languages'));
    }

    /** @param list<ReplaceLanguage> $languages the full new set; languages left out are removed */
    public function replace(array $languages): void
    {
        $this->pipeline->send(new ReplaceLanguagesRequest($languages));
    }
}
