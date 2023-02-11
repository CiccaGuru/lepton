<?php
namespace Lepton\Helpers;

class Functions{
static function getDeclarationLine(\ReflectionProperty $prop){
  $declaringClass = $prop->getDeclaringClass();
  $propname = $prop->getName();
  $classFile      = new \SplFileObject($declaringClass->getFileName());
  foreach ($classFile as $line => $content) {
      if (preg_match(
          '/
              (private|protected|public|var|static) # match visibility or var
              \s                             # followed 1 whitespace
              \$'.$propname.'                          # followed by the var name $bar
          /x',
          $content)
      ) {
          return $line + 1;
      }
  }
  return 1;
}
}
