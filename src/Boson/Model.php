<?php
namespace Lepton\Boson;
use Lepton\Exceptions;
use Lepton\Boson\DataTypes;


abstract class Model{

  private array $fields;
  private DataTypes\PrimaryKey $pk;
  private string $pkName;
  private bool $isLoadedFromDb;
  private array $editedFields;
  protected $tableName;

  function __construct(){

    $this->fields = array();
    $this->editedFields = array();

    $this->checkTableName();
    $this->checkFieldsAreProtected();
    $this->extractFieldsFromAttributes();
    $this->isLoadedFromDb = false;
    //$this->checkFields();
  }


  private function checkTableName(){
    if(!isset($this->tableName)){
      $class = new \ReflectionClass(get_class($this));
      throw new Exceptions\TableNameNotSetException($class);
    }
  }

  private function extractFieldsFromAttributes(){
    $maybeFields = (new \ReflectionClass(get_class($this)))->getProperties(
      \ReflectionProperty::IS_PROTECTED
    );


    foreach($maybeFields as $maybeField){

      if($fieldType = $this->getFieldType($maybeField)){
        if($fieldType->getName() == DataTypes\PrimaryKey::class ){
          if(isset($this->pk) ){
            throw new Exceptions\MultiplePrimaryKeyException($maybeField);
          } else {
            $this->pk = $fieldType->newInstance();
            $this->pkName = $maybeField->getName();
          }
        } else {
          $this->fields[$maybeField->getName()] = $fieldType->newInstance();
        }
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



  public function save(){
    $values = array_map(fn($value) => $this->$value, $this->editedFields);
    $toEdit = array_combine($this->editedFields, $values);
    $queryBuild = new QueryBuilder($this->tableName, $toEdit);
    if(!$this->isLoadedFromDb){
      $queryBuild->insert();
    } else {
      $queryBuild->update($this->{$this->pkName});
    }
  }

  public function __set($property, $value){
    if(array_key_exists($property, $this->fields) ){
      if($this->fields[$property]->validate($value)){
        $this->editedFields[] = $property;
        $this->editedFields = array_unique($this->editedFields);
        $this->$property = $value;
      } else{
        $className = get_class($this);
        throw new Exceptions\InvalidFieldException("Invalid value for field \"$property\" of $className: $value ");
      }
    }
    else{
      $className = get_class($this);
      throw new Exceptions\FieldNotFoundException("Cannot retrieve field \"$property\" of $className.");
    }
  }


  public function __toString(){
    $toPrint = "Model ".get_class($this).":<br/>";
    $toPrint .= $this->pkName." [Primary Key] => ".$this->{$this->pkName}."<br/>";
    foreach($this->fields as $k => $field){
      $toPrint .= "$k => ".$this->$k."<br/>";
    }

    return $toPrint;
  }
}