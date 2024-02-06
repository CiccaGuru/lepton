<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class TextField extends Field{


  private $max_length;
  private $nullable;

  public function __construct($max_length = 128, $nullable = false, ...$options ){
    $this->max_length = $max_length;
    $this->nullable = $nullable;
     parent::__construct(...$options);

  }

  public function validate($value){
    return true;
    if(is_null($value) && (!$this->nullable)) return false;
    if(strlen($value) > $this->max_length) return false;
    return true;
  }
}