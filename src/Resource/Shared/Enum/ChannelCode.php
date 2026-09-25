<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum ChannelCode: string
{
    case Direct = 'Direct';
    case BookingCom = 'BookingCom';
    case Ibe = 'Ibe';
    case ChannelManager = 'ChannelManager';
    case Expedia = 'Expedia';

    /** @deprecated no longer accepts new bookings or rate plans */
    case Homelike = 'Homelike';
    case Hrs = 'Hrs';
    case AltoVita = 'AltoVita';
    case DesVu = 'DesVu';
    case Gimsi = 'Gimsi';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
