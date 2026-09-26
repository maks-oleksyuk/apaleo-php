<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListAccountsRequest extends Request
{
    /** @param list<string> $accountCodes */
    public function __construct(
        private array $accountCodes = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/account/v1/accounts';
    }

    public function query(): array
    {
        return array_filter([
            'accountCodes' => Query::csv($this->accountCodes),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
