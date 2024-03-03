<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class TextField extends Field{



  public function __construct(private $max_length = 128, ...$options ){
     parent::__construct(...$options);

  }

  public function validate($value){
    if(strlen($value) > $this->max_length) return false;
    return parent::validate($value);
  }
}