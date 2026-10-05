<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

interface TokenProviderInterface
{
    public function getToken(bool $forceRefresh = false): AccessToken;
}
