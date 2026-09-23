<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Account\DTO\CreateAccount;

final readonly class CreateAccountRequest extends Request
{
    /** @param ?string $idempotencyKey lets a retried request be recognized and not create a duplicate account */
    public function __construct(
        private CreateAccount $account,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/account/v1/accounts';
    }

    public function body(): array
    {
        return $this->account->toArray();
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }
}
