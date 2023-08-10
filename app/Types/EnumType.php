<?php

namespace App\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumType extends Type
{
    const ENUM = 'enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return sprintf("ENUM('%s')", implode("','", $fieldDeclaration['allowed']));
    }

    public function getName()
    {
        return self::ENUM;
    }
}