<?php
namespace Lepton\Boson;

use Lepton\Base\Application;
use Lepton\Base\Database;

class QueryBuilder{

  public function __construct(protected string $tableName, protected array $fields){

  }

  public function insert(){

    $db = Application::getDb();
    $tableName =
    $fieldNames = implode(", ",array_keys($this->fields));
    $fieldPlaceholders = implode(", ", array_map(fn($value) => "?", $this->fields));
    $fieldValues = array_values($this->fields);

    print_r($fieldValues);
    $query = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->tableName, $fieldNames, $fieldPlaceholders);
    $result = $db->query($query, ...$fieldValues);

    //$result = $db->query("SELECT * from poll_answers where poll_id = ? and title = ?", 4, "Roberto Ciccarelli/");
    print_r($query);

  }


  public function update(int $pk){

  }
}