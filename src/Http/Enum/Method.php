<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Http\Enum;

enum Method: string
{
    case HEAD = 'HEAD';
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
}
