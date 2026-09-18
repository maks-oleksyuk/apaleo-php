<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum;

/** Filters units by archived state on list/count requests (query param `status`). */
enum UnitArchiveFilter: string
{
    case Active = 'Active';
    case Archived = 'Archived';
    case All = 'All';
}
