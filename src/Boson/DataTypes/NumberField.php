<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class NumberField extends Field{
  public function __construct(private int $max_length = 32, mixed ...$options){
    parent::__construct(...$options);
  }

  public function validate($value){
    if(is_null($value) && (!$this->null)) return false;
    return true;
  }
}