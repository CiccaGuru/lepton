<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class DateField extends Field{
  public function __construct(...$args){
    parent::__construct(...$args);
  }

  public function validate($value){
    return true;
  }

}