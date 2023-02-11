<?php
namespace Lepton\Exceptions;

use Exception;
use Throwable;
use Lepton\Helpers\Functions;

class MultipleFieldAttributeException extends FieldException{

  public function __construct(\ReflectionProperty $prop, $message = "Lo", $code = 0, ?\Throwable $previous = null){

    $propName = $prop->getName();
    $className = $prop->getDeclaringClass()->getName();

    $message =  "Multiple Field attributes: ".
                "Field '$propName' of '$className' has more than one Field Attribute.";

    parent::__construct($prop, $message, $code, $previous);
  }
}