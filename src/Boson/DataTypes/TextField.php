<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class TextField extends Field{


  private $max_length;
  private $nullable;

  public function __construct($max_length = 32, $nullable = false ){
    $this->max_length = $max_length;
    $this->nullable = $nullable;
  }

  public function validate($value){
    if(is_null($value) && (!$this->nullable)) return false;
    if(strlen($value) > $this->max_length) return false;
    return true;
  }
}