<?php

namespace Lepton\Boson\DataTypes;

use Lepton\Boson\Model;

#[\Attribute]
class ManyToMany extends Relationship
{
    public function __construct(public string $child, mixed ...$options)
    {
        parent::__construct($child, ...$options);
    }

    public function validate($value)
    {
        return true;
    }
}
