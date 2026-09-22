<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Enum;

enum OptionalCutoffBehavior: string
{
    case DoNothing = 'DoNothing';
    case AutoRelease = 'AutoRelease';
}
