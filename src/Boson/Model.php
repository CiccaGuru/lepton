<?php
namespace Lepton\Boson;
use Lepton\Exceptions;
use Lepton\Boson\DataTypes;


abstract class Model{
  public static $connection;
  private array $fields;

  function __construct(){

    $this->fields = array();
    $this->checkFieldsAreProtected();
    $this->extractFieldsFromAttributes();
    //$this->checkFields();
  }



  private function extractFieldsFromAttributes(){
    $maybeFields = (new \ReflectionClass(get_class($this)))->getProperties(
      \ReflectionProperty::IS_PROTECTED
    );


    foreach($maybeFields as $maybeField){

      if($fieldType = $this->getFieldType($maybeField)){
        $this->fields[$maybeField->getName()] = $fieldType->newInstance();
      }
    }
  }




  private function getFieldType($prop){

    $attributes = $prop->getAttributes();
    $fieldType = NULL;

    if(empty($attributes)){
      return false;
    }

    foreach($attributes as $k => $attribute){

      if(is_subclass_of(($attribute->getName()), DataTypes\Field::class)){
        if(is_null($fieldType))
          $fieldType =  $attribute;
        else
          throw new Exceptions\MultipleFieldAttributeException($prop);
      }
    }
    return $fieldType ?? false;
  }



  private function checkFieldsAreProtected(){
    $properties = (new \ReflectionClass(get_class($this)))->getProperties(
      \ReflectionProperty::IS_PUBLIC |
      \ReflectionProperty::IS_READONLY |
      \ReflectionProperty::IS_STATIC
    );
    foreach($properties as $prop){
      $attributes = $prop->getAttributes();
      foreach($attributes as $attr){
        if(is_subclass_of(($attr->getName()), DataTypes\Field::class)){
          throw new Exceptions\InvalidFieldVisibilityKeyword($prop);
        }
      }
    }
  }







  public function __get($property){
    $childClass = get_class($this);
    if(property_exists($childClass, $property))
      return $this->$property;
    else
      throw new Exceptions\FieldNotFoundException("Model '$childClass' has no field '$property'.");
  }




  public function __set($property, $value){
    echo $property." ".$value."<br/>";
    if(array_key_exists($property, $this->fields) ){
      if($this->fields[$property]->validate($value))
        $this->$property = $value;
      else{
        $className = get_class($this);
        throw new Exceptions\InvalidFieldException("Invalid value for field \"$property\" of $className: $value ");
      }
    }
    else{
      $className = get_class($this);
      throw new Exceptions\FieldNotFoundException("Cannot retrieve field \"$property\" of $className.");
    }
  }

}