<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

/** 5xx: Apaleo's own failure, retry-able by the caller. */
class ApaleoServerException extends ApaleoException
{
}
