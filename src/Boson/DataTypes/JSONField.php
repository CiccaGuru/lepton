<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class JSONField extends Field{


  private $nullable;

  public function __construct($nullable = false, ...$options ){
    $this->nullable = $nullable;
     parent::__construct(...$options);

  }

  public function validate($value){
    return true;
    if(is_null($value) && (!$this->nullable)) return false;
  }
}