<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Guest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\IdentificationType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\RelationshipToPrimaryGuest;
use Oleksyuk\Apaleo\Resource\Logs\DTO\ReservationChangedChange;
use Oleksyuk\Apaleo\Resource\Shared\Enum\Gender;
use Oleksyuk\Apaleo\Resource\Shared\Enum\Title;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TravelPurpose;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class EnumFallbackTest extends TestCase
{
    /**
     * A bare Enum::from()/tryFrom() on response data throws \ValueError (outside ApaleoExceptionInterface)
     * or silently nulls the moment Apaleo adds a value; response mapping must go through fromApi().
     */
    public function testResponseMappingNeverCallsFromOrTryFromOnEnumsDirectly(): void
    {
        $offenders = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../src', \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            if ($file->getExtension() !== 'php' || str_contains($file->getPath(), \DIRECTORY_SEPARATOR.'Enum')) {
                continue;
            }

            $code = (string) file_get_contents($file->getPathname());
            if (preg_match_all('/\b[A-Z]\w*::(?:from|tryFrom)\(/', $code, $matches) > 0) {
                $offenders[] = $file->getPathname().': '.implode(', ', $matches[0]);
            }
        }

        self::assertSame([], $offenders);
    }

    public function testUnrecognizedValueFallsBackToUnknownInsteadOfThrowing(): void
    {
        $target = AuthorizationTarget::fromArray(['type' => 'SomethingNew', 'id' => 'X', 'propertyId' => 'MUC']);
        self::assertSame(AuthorizationTargetType::Unrecognized, $target->type);

        $change = ReservationChangedChange::fromArray(['travelPurpose' => 'SomethingNew', 'guaranteeType' => 'PM6Hold']);
        self::assertSame(TravelPurpose::Unrecognized, $change->travelPurpose);
    }

    public function testGuestParsesEnumsFallsBackOnNewValuesAndSerializesBack(): void
    {
        $guest = Guest::fromArray(['lastName' => 'Doe', 'title' => 'Mr', 'gender' => 'Male', 'identificationType' => 'PassportNumber', 'relationshipToPrimaryGuest' => 'Spouse']);
        self::assertSame(Title::Mr, $guest->title);
        self::assertSame(Gender::Male, $guest->gender);
        self::assertSame(IdentificationType::PassportNumber, $guest->identificationType);
        self::assertSame(RelationshipToPrimaryGuest::Spouse, $guest->relationshipToPrimaryGuest);
        self::assertSame(['lastName' => 'Doe', 'title' => 'Mr', 'gender' => 'Male', 'identificationType' => 'PassportNumber', 'relationshipToPrimaryGuest' => 'Spouse'], $guest->toArray());

        $new = Guest::fromArray(['lastName' => 'Doe', 'identificationType' => 'SomethingNew', 'relationshipToPrimaryGuest' => 'SomethingNew']);
        self::assertSame(IdentificationType::Unrecognized, $new->identificationType);
        self::assertSame(RelationshipToPrimaryGuest::Unrecognized, $new->relationshipToPrimaryGuest);
    }
}
