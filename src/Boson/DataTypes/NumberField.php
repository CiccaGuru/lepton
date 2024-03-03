<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class NumberField extends Field{
  public function __construct(mixed ...$options){
    parent::__construct(...$options);
  }

  public function validate($value){
    if(!is_numeric($value)) return false;
    return parent::validate($value);
  }
}