<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class DateTimeField extends Field{
  public function __construct(){
  }

  public function validate($value){
    return true;
  }
}