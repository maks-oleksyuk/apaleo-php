<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\Language\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetLanguagesRequest extends Request
{
    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/languages';
    }
}
