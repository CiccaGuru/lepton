<?php
namespace Lepton\Exceptions;

use Exception;
use Throwable;
use Lepton\Helpers\Functions;

class NoPrimaryKeyException extends \Exception{

  public function __construct(\ReflectionClass $class, $message = "", $code = 0, ?\Throwable $previous = null){

    $className = $class->getName();
    $this->line = $class->getStartLine();
    $this->file = $class->getFileName();

    $message =  "No PrimaryKey: ".
                "Model '$className' has no Primary Key.";

    parent::__construct($message, $code, $previous);
  }
}