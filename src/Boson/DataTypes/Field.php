<?php
namespace Lepton\Boson\DataTypes;


class Field{

  protected array $default_error_messages = array(
    "invalid_choice"  => "Value %value is not a valid choice.",
    "null"            => "This field cannot be null.",
    "blank"           => "This field cannot be blank.",
    "unique"          => "A %model_name with this %field_label already exists.",
    "unique_for"      => "Field %field_label must be unique for fields with same %unique_for."
  );

  protected array $validation_errors = array();

  public function __construct(
     protected bool   $null             = false,
     protected bool   $blank            = true,
     protected array  $choices          = array(),
     protected string $db_column        = "",
     protected mixed  $default          = "",
     protected bool   $editable         = true,
     protected array  $error_messages   = array(),
     protected string $help_text        = "",
     protected bool   $primary_key      = false,
     protected bool   $unique           = false,
     protected string $verbose_name     = "",
     protected array  $validators       = array()
    )
  {
    return;
  }


  protected function validate($value){
    if (is_null($value) && (!$this->null)) return false;
    if (empty($value) && (!$this->blank)) return false;
    if (!empty($this->choices) && !in_array($value, $this->choices)) return false;
    if (!empty($this->validators)) return $this->validate_with_validators($value);

    return true;
  }


  private function validate_with_validators($value){
    foreach($this->validators as $validator){
      if (!$validator($value)) return false;
    }
    return true;
  }



  public function db_column(): string{
    return $this->db_column;
  }

  public function set_db_column(string $column_name): void{
    $this->db_column = $column_name;
  }
}