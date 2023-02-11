<?php
namespace Lepton\Exceptions;

use Exception;

class TableNameNotSetException extends Exception{

  public function __construct(\ReflectionClass $class, $message = "", $code = 0, ?\Throwable $previous = null){

    $className = $class->getName();

    $message =  "No Table Name: ".
                "Model '$className' has no table name set.";

    $this->line = $class->getStartLine();
    $this->file = $class->getFileName();
    parent::__construct($message, $code, $previous);
  }
}