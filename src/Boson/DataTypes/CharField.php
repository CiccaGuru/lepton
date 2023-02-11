<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class CharField extends Field{
  public function __construct(private int $max_length = 32, mixed ...$options){
    parent::__construct(...$options);
    echo $this->null;
  }

  public function validate($value){
    if(is_null($value) && (!$this->null)) return false;
    if(strlen($value) > $this->max_length) return false;
    return true;
  }
}