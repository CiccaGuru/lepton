<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class DateTimeField extends Field{
  public function __construct(...$args){
    parent::__construct(...$args);
  }

  public function validate($value){
    if (!preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $value)) return false;
    return parent::validate($value);
  }
}