<?php
namespace Lepton\Boson\DataTypes;

#[\Attribute]
class JSONField extends Field{


  public function __construct(...$options ){
     parent::__construct(...$options);

  }

  public function validate($value){
    if(!is_array(json_decode($value, true))) return false;
    return parent::validate($value);
  }
}