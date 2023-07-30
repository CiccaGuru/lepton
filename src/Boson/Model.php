<?php

namespace Lepton\Boson;

use Lepton\Exceptions;
use Lepton\Boson\DataTypes;
use Lepton\Base\Application;

abstract class Model
{
    /**
     * The fields for the model. This array stores the actual field values.
     * @var array
     */
    private array $fields;

    /**
     * Map between fields' names and table columns' names
     */
    private array $tableColumns;


    /**
     * The relationships of the model.
     * @var array
     */

    private array $relationships;


    /**
     * The names of the database columns for the fields.
     * @var arrat
     */

    private array $db_columns;


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
    public function __construct()
    {
        $this->fields = array();
        $this->relationships = array();
        $this->db_columns = array();
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
    private function checkTableName()
    {
        if (!isset(static::$tableName)) {
            $class = new \ReflectionClass(get_class($this));
            try {
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
    private function extractFieldsFromAttributes()
    {
        $maybeFields = (new \ReflectionClass(get_class($this)))->getProperties(
            \ReflectionProperty::IS_PROTECTED
        );


        foreach ($maybeFields as $maybeField) {
            if ($fieldType = $this->getFieldType($maybeField)) {
                $field = $fieldType->newInstance();


                // Check if it's PrimaryKey
                if ($fieldType->getName() == DataTypes\PrimaryKey::class) {
                    // A model must have only one Primary Key
                    if (isset($this->pk)) {
                        throw new Exceptions\MultiplePrimaryKeyException($maybeField);
                    } else {
                        $this->pk = $field;
                        $this->pkName = $maybeField->getName();

                        // If column name is not set, use field name
                        if($field->db_column() == "") {
                            $field->set_db_column($maybeField->getName());
                        }
                    }
                } elseif ($fieldType->getName() == DataTypes\ForeignKey::class) {
                    $this->relationships[$maybeField->getName()] = $field;
                    // If column name is not set, get parent Primary Key name
                    if($field->db_column() == "") {
                        $props = (new \ReflectionClass($field->parent))->getProperties(\ReflectionProperty::IS_PROTECTED);
                        $parentPkName = "";
                        foreach($props as $prop) {
                            $attributes = $prop->getAttributes(DataTypes\PrimaryKey::class);
                            if(count($attributes)) {
                                $parentPkName = $prop->getName();
                            }
                        }
                        $field->set_db_column($maybeField->getName()."_".$parentPkName);

                    }

                } else {
                    $this->fields[$maybeField->getName()] = $field;

                    // If column name is not set, use field name
                    if($field->db_column() == "") {
                        $field->set_db_column($maybeField->getName());
                    }
                }

                $this->db_columns[$maybeField->getName()] = $field->db_column();

            }
        }

        // A model must have a PrimaryKey
        if (!isset($this->pk)) {
            throw new Exceptions\NoPrimaryKeyException(new \ReflectionClass(get_class($this)));
        }
    }


    final public function isRelationship($prop): bool
    {
        return array_key_exists($prop, $this->relationships);
    }

    /**
     *
     */

    final public function getRelationshipParentModel($prop): string
    {
        return $this->relationships[$prop]->parent;
    }

    /**
     * Analyze a property's attributes to check if it's a field.
     * If it's a field, returns the field type.
     * If it's not, returns true.
     *
     * @param \ReflectionProperty $prop
     * @return bool|Object
     */
    final public function getFieldType($prop)
    {
        $attributes = $prop->getAttributes();
        $fieldType = null;

        if (empty($attributes)) {
            return false;
        }

        foreach ($attributes as $k => $attribute) {
            if (is_subclass_of(($attribute->getName()), DataTypes\Field::class)) {
                // A field should have only one field type
                if (is_null($fieldType)) {
                    $fieldType =  $attribute;
                } else {
                    throw new Exceptions\MultipleFieldAttributeException($prop);
                }
            }
        }
        return $fieldType ?? false;
    }


    final public function db_columns(){
        return $this->db_columns;
    }

    /**
     * Check that all the attributes that have a Field attribute are declared as protected
     *
     * @return void
     */
    private function checkFieldsAreProtected()
    {
        $properties = (new \ReflectionClass(get_class($this)))->getProperties(
            \ReflectionProperty::IS_PUBLIC |
            \ReflectionProperty::IS_READONLY |
            \ReflectionProperty::IS_STATIC
        );
        foreach ($properties as $prop) {
            $attributes = $prop->getAttributes();
            foreach ($attributes as $attr) {
                if (is_subclass_of(($attr->getName()), DataTypes\Field::class)) {
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
    final public function __get(string $property)
    {
        $camelcase = preg_replace_callback("/(?:^|_)([a-zA-Z])/", fn ($x) => strtoupper($x[1]), $property);
        if (array_key_exists($property, $this->fields)) {
            return $this->$property;
        } elseif (array_key_exists($property, $this->relationships)) {
            $parent = $this->relationships[$property]->parent::get($this->$property);
            return $parent;
        } elseif ($property == $this->pkName) {
            return $this->getPk();
        } elseif(class_exists("App\Models\\".$camelcase)) {
            $class = new \ReflectionClass(get_class($this));
            $className =  explode("\\", $class->getName());
            $columnName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));
            $foreignKey = (new \ReflectionClass("App\Models\\".$camelcase))->getProperty($columnName);
            $a = $foreignKey->getAttributes("Lepton\Boson\DataTypes\ForeignKey");
            if(count($a) == 1) {
                $parameters = [$columnName => $this->getPk()];
                return ("App\Models\\".$camelcase)::filter(...$parameters);
            } else {
                throw new \Exception("Referenced Model has no ForeignKey to traverse the relationship");
            }
        }
        throw new Exceptions\FieldNotFoundException("Model has no field '$property'.");

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
    final public function __set(string $property, mixed $value)
    {
        $this->setValue($property, $value);
    }

    final public function setValue(string $property, mixed $value)
    {
        if (array_key_exists($property, $this->fields)) {
            if ($this->setEditedField($property, $value, $this->fields)) {
                $this->$property = $value;
            }
        } elseif (array_key_exists($property, $this->relationships)) {
            if (is_int($value)) {
                $value = ($this->relationships[$property]->parent)::get($value);
            }
            if ($this->relationships[$property]->parent == $value::class) {
                if ($this->setEditedField($property, $value->getPk(), $this->relationships)) {
                    $this->$property = $value;
                } else {
                    throw new \Exception("Error in setting relationship value");
                }
            } else {
                throw new \Exception(sprintf("Given model is wrong type, expecting %, % given", $this->relationships[$property]->parent, $value::class));
            }
        } elseif ($property == $this->pkName) {
            $this->$property = $value;
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
    private function setEditedField(mixed $property, mixed $value, array &$container): bool
    {
        if ($container[$property]->validate($value)) {
            $this->editedFields[] = $property;
            $this->editedFields = array_unique($this->editedFields);
            return true;
        } else {
            $className = get_class($this);
            throw new Exceptions\InvalidFieldException("Invalid value for field \"$property\" of $className: $value ");
        }
        return false;
    }


    final public function __toString()
    {
        $toPrint = "Model ".get_class($this).":<br/>";
        $toPrint .= $this->pkName." [Primary Key] => ".$this->{$this->pkName}."<br/>";
        foreach ($this->fields as $k => $field) {
            $toPrint .= "$k => ".$this->$k."<br/>";
        }

        foreach ($this->relationships as $k => $field) {
            $parent = new $field->parent();
            $class = explode("\\", $field::class);
            $toPrint .= "$k => ".end($class)." (".$parent->getPkName()."=".$this->$k.")<br/>";
        }


        return $toPrint;
    }



    final public function getPk()
    {
        return $this->{$this->pkName};
    }

    final public function getPkName()
    {
        return $this->pkName;
    }

    final public static function getTableName()
    {
        return static::$tableName;
    }

    final public function getColumnList(): array
    {
        foreach ($this->db_columns as $column) {
            $columns[] = $column;
        }

        return $columns;
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

    public static function new(...$args)
    {
        $class = new \ReflectionClass(get_called_class());
        $model = new ($class->getName())();
        foreach ($args as $prop => $value) {
            $model->setValue($prop, $value);
        }

        //$model->clearEditedFields();
        return $model;
    }


    public function load(...$args){
        foreach ($args as $prop => $value) {
            $this->$prop = $value;
        }
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
    final public function save()
    {
        // If there's a primary key value, try to update
        if (isset($this->{$this->pkName})) {
            $this->update();
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
    private function update()
    {
        $db = Application::getDb();


        $values = array_map(array($this, "getFieldValues"), $this->editedFields);
        array_push($values, $this->getPk());

        $fieldsString = implode(", ", array_map(fn ($value) => $this->getFieldName($value)." = ?", $this->editedFields));
        $query = sprintf("UPDATE `%s` SET %s WHERE %s = ?", static::$tableName, $fieldsString, $this->getFieldName($this->pkName));
        $result = $db->query($query, ...$values);
        return $result->affected_rows();
    }



    /**
     * Insert the data in the database using prepared queries.
     *
     * @return int
     * Last inserted id
     */
    private function insert()
    {
        $db = Application::getDb();

        $values = array_map(array($this, "getFieldValues"), $this->editedFields);

        $fieldsString = implode(", ", array_map(array($this, "getFieldName"), $this->editedFields));
        $placeholders = implode(", ", array_fill(0, count($this->editedFields), "?"));
        $query = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", static::$tableName, $fieldsString, $placeholders);
        //        die($query);
        $result = $db->query($query, ...$values);

        return $result->last_insert_id();
    }


    final public function delete()
    {
        $db = Application::getDb();
        $query = sprintf("DELETE FROM `%s` WHERE %s = ?", static::$tableName, $this->pkName);
        $pk = $this->getPk();
        $db->query($query, $pk);
        return;
    }

    private function getFieldName($field): string
    {
        return $this->db_columns[$field];
        /*if (array_key_exists($field, $this->fields)) {
            return $field;
        } elseif (array_key_exists($field, $this->relationships)) {
            return $field."_".($this->$field)->getPkName();
        }*/
    }

    private function getFieldValues($field): mixed
    {
        if (array_key_exists($field, $this->fields)) {
            return $this->$field;
        } elseif (array_key_exists($field, $this->relationships)) {
            return ($this->$field)->getPk();
        }
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


    public static function get(...$filters): Model|bool
    {
        if ((count($filters) == 1) && array_key_first($filters)  == 0) {
            $filters = array((new static())->pkName => $filters[0]);
        }

        $querySet = static::filter(...$filters);



        $result = $querySet->do();
        if ($result->count() == 0) {
            return false;//throw new \Exception("No result found");
        } elseif ($result->count() > 1) {
            throw new \Exception("Only one result allowed when using get, multiple obtained");
        } else {
            foreach ($result as $model) {
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
    final public static function __callStatic(string $name, iterable $arguments): QuerySet
    {
        $querySet = new QuerySet(static::class);
        $querySet->$name(...$arguments);
        return $querySet;
    }


    final public function clearEditedFields()
    {
        $this->editedFields = array();
    }
}
