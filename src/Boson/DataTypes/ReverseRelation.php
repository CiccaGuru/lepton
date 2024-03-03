<?php

namespace Lepton\Boson\DataTypes;

use Lepton\Boson\Model;

#[\Attribute]
class ReverseRelation extends Relationship
{
    public function __construct(public string $child, public string $foreignKey, mixed ...$options)
    {
        parent::__construct($child, ...$options);
    }

    public function validate($value)
    {
        return parent::validate($value);
    }
}
