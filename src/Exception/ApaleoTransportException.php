<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

/** Network-level failure (timeout, DNS, connection refused) — never reached Apaleo. */
class ApaleoTransportException extends \RuntimeException
{
}
