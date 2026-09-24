<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\DTO;

use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\Enum\CapturePaymentMode;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Item shape of GET /settings/v1/capture-policies: unlike CapturePolicy, $code is always set. */
final readonly class CapturePolicyListItem
{
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public bool $captureNoShowFee,
        public bool $captureCancellationFee,
        public bool $capturePrepayment,
        public bool $postOtaBankTransferOnCheckOut,
        public CapturePaymentMode $capturePayment,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            captureNoShowFee: ResponseData::bool($data, 'captureNoShowFee'),
            captureCancellationFee: ResponseData::bool($data, 'captureCancellationFee'),
            capturePrepayment: ResponseData::bool($data, 'capturePrepayment'),
            postOtaBankTransferOnCheckOut: ResponseData::bool($data, 'postOtaBankTransferOnCheckOut'),
            capturePayment: CapturePaymentMode::fromApi(ResponseData::string($data, 'capturePayment')),
        );
    }
}
