<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

enum RelationshipToPrimaryGuest: string
{
    case Grandparent = 'Grandparent';
    case Grandchild = 'Grandchild';
    case GreatGrandparent = 'GreatGrandparent';
    case GreatGrandchild = 'GreatGrandchild';
    case Parent = 'Parent';
    case Child = 'Child';
    case Sibling = 'Sibling';
    case ParentInLaw = 'ParentInLaw';
    case ChildInLaw = 'ChildInLaw';
    case SiblingInLaw = 'SiblingInLaw';
    case Spouse = 'Spouse';
    case UncleOrAunt = 'UncleOrAunt';
    case NephewOrNiece = 'NephewOrNiece';
    case Guardian = 'Guardian';
    case Other = 'Other';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
