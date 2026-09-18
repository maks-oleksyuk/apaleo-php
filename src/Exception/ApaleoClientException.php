<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Exception;

/** 4xx: caller did something wrong (bad input, missing resource, auth, rate limit). */
class ApaleoClientException extends ApaleoException {}
