<?php

namespace Lepton\Boson\DataTypes;

use Lepton\Boson\Model;

#[\Attribute]
class ManyToMany extends Relationship
{
    public function __construct(public string $child, public string $throughModel, mixed ...$options)
    {
        parent::__construct($child, ...$options);
    }

    public function validate($value)
    {
        if (!is_a($value, $this->child)) return false;
        return parent::validate($value);
    }
}
