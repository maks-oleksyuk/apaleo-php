<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum NightAuditStatus: string
{
    case InProgress = 'InProgress';
    case Success = 'Success';
    case Failure = 'Failure';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
