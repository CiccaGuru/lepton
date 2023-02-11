<?php
namespace Lepton\Exceptions;

use Exception;
use Throwable;
use Lepton\Helpers\Functions;

class MultiplePrimaryKeyException extends FieldException{

  public function __construct(\ReflectionProperty $prop, $message = "Lo", $code = 0, ?\Throwable $previous = null){

    $propName = $prop->getName();
    $className = $prop->getDeclaringClass()->getName();

    $message =  "Multiple PrimaryKey: ".
                "Field '$propName' of '$className' has more than one Primary Key.";

    parent::__construct($prop, $message, $code, $previous);
  }
}