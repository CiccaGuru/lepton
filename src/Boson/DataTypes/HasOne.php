<?php

namespace Lepton\Boson\DataTypes;

use Lepton\Boson\Model;

#[\Attribute]
class HasOne extends Relationship
{
    public function __construct(public string $sibling, mixed ...$options)
    {
        parent::__construct($sibling, ...$options);
    }

    public function validate($value)
    {
        return true;
    }
}
