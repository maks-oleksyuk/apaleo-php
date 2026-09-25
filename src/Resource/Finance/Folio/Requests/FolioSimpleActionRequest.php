<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Shared shape for the folio-actions endpoints that take no body: close, reopen, post-charges. */
final readonly class FolioSimpleActionRequest extends Request
{
    /** @param 'close'|'post-charges'|'reopen' $action */
    public function __construct(
        private string $folioId,
        private string $action,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/folio-actions/'.rawurlencode($this->folioId).'/'.$this->action;
    }
}
