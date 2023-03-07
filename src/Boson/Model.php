<?php
namespace Lepton\Boson;
use Lepton\Exceptions;
use Lepton\Boson\DataTypes;
use Lepton\Base\Application;


abstract class Model{

  /**
   * The fields for the model. This array stores the actual field values.
   * @var array
   */
  private array $fields;


  /**
   * The relationships of the model.
   * @var array
   */

  private array $relationships;

  /**
   * The primary key. It's not the actual value
   * @var DataTypes\PrimaryKey
   */
  private DataTypes\PrimaryKey $pk;


  /**
   * The name of the field containing the actual Primary Key value
   * @var string
   */
  private string $pkName;


  /**
   * The list of edited field since last database sync.
   * @var array
   */
  private array $editedFields;

  /**
   * The name of the table in the database.
   * It can be overloaded by the implementation.
   *
   * @var string
   */
  protected static $tableName;


  /**
   * Create a new Model instance
   *
   * @return void
   */
  function __construct(){

    $this->fields = array();
    $this->relationships = array();
    $this->editedFields = array();

    $this->checkTableName();
    $this->checkFieldsAreProtected();
    $this->extractFieldsFromAttributes();
    return $this;
  }

  /**
   * Check if table name is set. If not set, get it by transforming class name to sneak-case
   * and putting an underscore _ between words
   *
   * @return void
   */
  private function checkTableName(){
    if(!isset(static::$tableName)){
      $class = new \ReflectionClass(get_class($this));
      try{
        $className =  explode("\\", $class->getName());
        static::$tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));
      } catch (\Exception $e) {
        throw new Exceptions\TableNameNotSetException($class);
      }
    }
  }


  /**
   * Analyze all the protected properties of the Model.
   *
   * If a property is an instance of a class extending Lepton\Boson\DataTypes\Field,
   * treat it as a field.
   *
   * @return void
   */
  private function extractFieldsFromAttributes(){
    $maybeFields = (new \ReflectionClass(get_class($this)))->getProperties(
      \ReflectionProperty::IS_PROTECTED
    );


    foreach($maybeFields as $maybeField){

      if($fieldType = $this->getFieldType($maybeField)){

        // Check if it's PrimaryKey
        if($fieldType->getName() == DataTypes\PrimaryKey::class ){

          // A model must have only one Primary Key
          if(isset($this->pk) ){
            throw new Exceptions\MultiplePrimaryKeyException($maybeField);
          } else {
            $this->pk = $fieldType->newInstance();
            $this->pkName = $maybeField->getName();
          }
        } else if($fieldType->getName() == DataTypes\ForeignKey::class){
          $this->relationships[$maybeField->getName()] = $fieldType->newInstance();
        } else {
          $this->fields[$maybeField->getName()] = $fieldType->newInstance();
        }
      }
    }

    // A model must have a PrimaryKey
    if(!isset($this->pk)){
      throw new Exceptions\NoPrimaryKeyException(new \ReflectionClass(get_class($this)));
    }
  }


  /**
   * Analyze a property's attributes to check if it's a field.
   * If it's a field, returns the field type.
   * If it's not, returns true.
   *
   * @param \ReflectionProperty $prop
   * @return bool|Object
   */
  private function getFieldType($prop) {

    $attributes = $prop->getAttributes();
    $fieldType = NULL;

    if(empty($attributes)){
      return false;
    }

    foreach($attributes as $k => $attribute){

      if(is_subclass_of(($attribute->getName()), DataTypes\Field::class)){
        // A field should have only one field type
        if(is_null($fieldType))
          $fieldType =  $attribute;
        else
          throw new Exceptions\MultipleFieldAttributeException($prop);
      }
    }
    return $fieldType ?? false;
  }


  /**
   * Check that all the attributes that have a Field attribute are declared as protected
   *
   * @return void
   */
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






  /**
   * When a Field is requested, return the corresponding element in $this->fields
   *
   * @param string $property
   * The property name
   *
   * @return mixed
   */
  public final function __get(string $property){
    $childClass = get_class($this);
    if(property_exists($childClass, $property))
      return $this->$property;
    else
      throw new Exceptions\FieldNotFoundException("Model '$childClass' has no field '$property'.");
  }

  /**
   * When setting a Field, check if the provided $value is valid.
   * Then set the corresponding element of $this->checkFieldsAreProtected
   *
   * @param string $property
   * The property name
   *
   * @param mixed $value
   * The value to be set
   *
   * @return void
   */
  public final function __set(string $property, mixed $value){
    if(array_key_exists($property, $this->fields) ){
      if($this->setEditedField($property, $value, $this->fields)){
        $this->$property = $value;
      }
    }
    else if(array_key_exists($property, $this->relationships)){
      if ($this->relationships[$property]->parent == $value::class ){
        if($this->setEditedField($property, $value->getPk(), $this->relationships)){
          $this->$property = $value;
        }
      } else {
        throw new \Exception(sprintf("Given model is wrong type, expecting %, % given", $this->relationships[$property]->parent, $value::class  ));
      }
    } else {
      $className = get_class($this);
      throw new Exceptions\FieldNotFoundException("Cannot retrieve field \"$property\" of $className.");
    }
  }


  /**
   * Set the value for edited field
   *
   * @param mixed $property
   * @param mixed $value
   * @param array $conteiner
   *
   * @return bool
   */
  private function setEditedField(mixed $property, mixed $value, array &$container):bool{
    if($container[$property]->validate($value)){
      $this->editedFields[] = $property;
      $this->editedFields = array_unique($this->editedFields);
      return true;
    } else{
      $className = get_class($this);
      throw new Exceptions\InvalidFieldException("Invalid value for field \"$property\" of $className: $value ");
    }
    return false;
  }


  public final function __toString(){
    $toPrint = "Model ".get_class($this).":<br/>";
    $toPrint .= $this->pkName." [Primary Key] => ".$this->{$this->pkName}."<br/>";
    foreach($this->fields as $k => $field){
      $toPrint .= "$k => ".$this->$k."<br/>";
    }
    foreach($this->relationships as $k => $field){
      $toPrint .= "$k => ".$this->$k::class."(".$this->$k->getPkName()."=".$this->$k->getPk().")<br/>";
    }

    return $toPrint;
  }



  public final function getPk(){
    return $this->{$this->pkName};
  }

  public final function getPkName(){
    return $this->pkName;
  }

  public final static function getTableName(){
    return static::$tableName;
  }


  /**
   * Statically initialize the model
   *
   * @param mixed ...$args
   * The values to be put in the fields.
   *
   * @return object $model
   * The model
   */

  public static function new(...$args){
    $class = new \ReflectionClass(get_called_class());
    $model = new ($class->getName());

    foreach ($args as $prop => $value){
      $model->$prop = $value;
    }

    return $model;
  }


  /*
  ======================================================================================
  ***************************** DATABASE INTERACTION ***********************************
  ======================================================================================
  */


  /**
   * Save the model to the database.
   * This function is just a wrapper around insert and update.
   *
   * @return void
   */
  public final function save(){

    // If there's a primary key value, try to update
    if(isset($this->{$this->pkName}) && $this->update()){
      $this->editedFields = array();
      return;
    }

    $this->{$this->pkName} = $this->insert();

    // All fields are now saved
    $this->editedFields = array();
  }


  /**
   * Update the data in the database using prepared queries.
   *
   * @return bool
  */
  private function update(){
    $db = Application::getDb();

    $values = array_map(array($this, "getFieldValues"),  $this->editedFields);
    array_push($values, $this->getPk());

    $fieldsString = implode(", ", array_map(fn($value) => "$value = ?", $this->editedFields));
    $query = sprintf("UPDATE`%s` SET %s WHERE %s = ?", static::$tableName, $fieldsString,  $this->pkName);

    $result = $db->query($query, ...$values);
    return $result->affected_rows();
  }



  /**
   * Insert the data in the database using prepared queries.
   *
   * @return int
   * Last inserted id
   */
  private function insert(){


    $db = Application::getDb();

    //die(print_r($this->editedFields));

    $values = array_map(array($this, "getFieldValues"),  $this->editedFields);

    $fieldsString = implode(", ",  array_map(array($this, "getFieldName"), $this->editedFields));
    $placeholders = implode(", ", array_fill(0, count( $this->editedFields), "?"));
    $query = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", static::$tableName, $fieldsString, $placeholders);

    $result = $db->query($query, ...$values);

    return $result->last_insert_id();
  }

  private function getFieldName($field):string{
    if(array_key_exists($field, $this->fields))
      return $field;
    else if(array_key_exists($field, $this->relationships))
      return $field."_".($this->$field)->getPkName();
  }

  private function getFieldValues($field): mixed{
      if(array_key_exists($field, $this->fields))
        return $this->$field;
      else if(array_key_exists($field, $this->relationships))
        return ($this->$field)->getPk();
  }

  /**
   * Get a unique result from the database.
   *
   * @param mixed $filters
   * The filters to be applied.  If $filters has only one element and no key, it is
   * supposed to be the primary key.
   *
   * @return Model
   * The model fulfilling the filters
   */


  public static function get(...$filters): Model{

    if((count($filters) == 1) && array_key_first($filters)  == 0){
      $filters = array((new static())->pkName => $filters[0]);
    }

    $querySet = static::filter(...$filters);
    $result = $querySet->do();
    if($result->count() == 0){
      throw new \Exception("No result found");
    } else if( $result->count() > 1){
      throw new \Exception("Only one result allowed when using get, multiple obtained");
    } else {
      foreach($result as $model){
        return $model;
      }
    }

  }

  /**
   * Provide an interface to call the corresponding function of QuerySet
   *
   * @param string $name
   * The name of the function to be called
   *
   * @param iterable $arguments
   * The arguments to be passed to the function.
   *
   * @return QuerySet
   * The QuerySet that represents the result.
   */
  public static final function __callStatic(string $name, iterable $arguments): QuerySet
  {
    $querySet = new QuerySet(static::class);
    $querySet->$name(...$arguments);
    return $querySet;
  }


}