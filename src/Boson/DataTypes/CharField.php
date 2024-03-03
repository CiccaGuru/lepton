<?php

namespace Lepton\Boson\DataTypes;

#[\Attribute]
class CharField extends Field
{
    public function __construct(private int $max_length = 32, mixed ...$options)
    {
        parent::__construct(...$options);
    }

    public function validate($value)
    {
        if(strlen($value) > $this->max_length) return false;
        return parent::validate($value);
    }
}
