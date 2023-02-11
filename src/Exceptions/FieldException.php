<?php
namespace Lepton\Exceptions;

use Exception;
use Throwable;
use Lepton\Helpers\Functions;

class FieldException extends Exception{

  public function __construct(\ReflectionProperty $prop, $message = "", $code = 0, ?\Throwable $previous = null){

    $this->line = Functions::getDeclarationLine($prop);
    $this->file = $prop->getDeclaringClass()->getFileName();
    parent::__construct($message, $code, $previous);
  }
}