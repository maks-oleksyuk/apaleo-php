<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Enum;

enum CapturePaymentMode: string
{
    case Manual = 'Manual';
    case CheckIn = 'CheckIn';
    case CheckOut = 'CheckOut';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
