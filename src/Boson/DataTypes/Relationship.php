<?php

namespace Lepton\Boson\DataTypes;

use Lepton\Boson\Model;

#[\Attribute]
class Relationship extends Field
{
    public function __construct(public string $table, mixed ...$options)
    {
        parent::__construct(...$options);
    }

    public function validate($value)
    {
        return true;
    }
}
