<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

/** The response body could not be parsed or mapped: invalid JSON, or a field with an unexpected shape. */
class ApaleoUnexpectedResponseException extends \RuntimeException implements ApaleoExceptionInterface {}
