<?php
namespace Lepton\Exceptions;

use Exception;
use Throwable;
use Lepton\Helpers\Functions;

class InvalidFieldVisibilityKeyword extends FieldException{

 public function __construct(\ReflectionProperty $prop, $message = "", $code = 0, ?\Throwable $previous = null){

    $propName = $prop->getName();
    $className = $prop->getDeclaringClass()->getName();
    $modifiers = implode(' ', \Reflection::getModifierNames($prop->getModifiers()));

    $message =  "Invalid visibility keyword: ".
                "Field '$propName' of '$className' set to '$modifiers', `protected` expected.";
    parent::__construct($prop, $message, $code, $previous);
  }
}