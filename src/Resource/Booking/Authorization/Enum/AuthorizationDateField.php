<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum;

enum AuthorizationDateField: string
{
    case Creation = 'Creation';
    case Modification = 'Modification';
    case Expiration = 'Expiration';
}
