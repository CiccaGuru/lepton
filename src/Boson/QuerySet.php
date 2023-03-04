<?php
namespace Lepton\Boson;

use Lepton\Base\Application;
use Lepton\Base\Database;
use mysqli_stmt;


/**
 * QuerySet is lazy: it doesn't execute queries unless forced.
 * It tries to build only one big query in order to accomplish the task.
 */

class QuerySet
  implements \Iterator, \ArrayAccess{

  private int $index;
  private mixed $current;
  private bool $valid;


  public array $filters;
  public array $modifiers;
  private array $lookup_map;

  private \mysqli_result $result;
  private array $cache;

  public function __construct(protected string $model){
    $this->lookup_map = array(
      "equals" => [
          "operator" => "=",
          "rhs" => fn($x) =>  $x
         ],
      "startswith" => [
          "operator" => "LIKE",
          "rhs" => fn($x) =>  sprintf('%s%%', $x)
        ],

      "endswith" => [
          "operator" => "LIKE",
          "rhs" => fn($x) => sprintf('%%%s', $x)
        ],

      "contains" => [
        "operator" => "LIKE",
        "rhs" => fn($x) => sprintf('%%%s%%', $x)
      ],

      "lte" => [
        "operator" => "<=",
        "rhs" => fn($x) => $x
      ],

      "gte" => [
        "operator" => ">=",
        "rhs" => fn($x) => $x
      ],

      "lt" => [
        "operator" => "<",
        "rhs" => fn($x) => $x
      ],

      "gt" => [
        "operator" => ">",
        "rhs" => fn($x) => $x
      ],

      "neq" => [
        "operator" => "<>",
        "rhs" => fn($x) => $x
      ]
    );

    $this->filters = array();
    $this->modifiers = array();

  }

  /*
  ================================== ITERATOR ==========================================
  */


  public function current(): mixed{
    return $this->current;
  }

  public function key(): mixed{
    return $this->index;
  }

  public function next(): void{
    $this->fetchNext();
  }

  public function rewind(): void{
    $this->index = -1;
    if(! isset($this->result)){
      $this->cache = array();
      $this->do();
    }
    $this->fetchNext();
  }

  public function valid(): bool{
    return $this->valid;
  }



  protected function fetchNext(){
    $items = $this->result->fetch_assoc();
    $this->valid = !is_null(($items));
    $this->current = $this->valid ? ($this->model)::new(...$items) : NULL;
    $this->index = $this->valid ? $this->current->getPk() : -1;
    $this->cache[$this->index] = $this->current;
  }





  /*
  ================================= ARRAY ACCESS =======================================
  */


  public function offsetExists(mixed $offset): bool{
    if(isset($this->result)){
      return array_key_exists($offset, $this->cache);
    } else {
      throw new \Exception("Before accessing keys, you have to execute query");
    }
  }


  public function  offsetGet(mixed $offset): mixed{
    if(isset($this->result)){
      return $this->cache[$offset];
    } else {
      throw new \Exception("Before accessing keys, you have to execute query");
    }
  }

  public function offsetSet(mixed $offset, mixed $value): void{
    throw new \Exception("QuerySets are read-only");
  }

  public function offsetUnset(mixed $offset): void{
    throw new \Exception("QuerySets are read-only");
  }


  /*
  ============================== DATABASE FUNCTIONS ====================================
  */


  private function extract_filters($filters) : array{
    $processed_filters = array();

    if((count($filters) == 1) && ($filters[array_key_first($filters)] instanceof QuerySet)){
      return $filters[array_key_first($filters)]->filters;
    } else if(count(array_filter($filters, fn($x) => $x instanceof QuerySet)) > 0){
      throw new \Exception("Only one element allowed if QuerySet");
    } else {
      return $filters;
    }
    return $processed_filters;
  }

  public function filter(...$filters): QuerySet{
    $this->and(...$filters);
    return $this;
  }


  public function and(...$filters): QuerySet{
    $this->filters[] = ["AND"  => $this->extract_filters($filters)];
    return $this;
  }


  public function exclude(...$filters): QuerySet{
    $this->filters[] = ["AND NOT"  => $this->extract_filters($filters)];
    return $this;
  }





  public function or(...$filters): QuerySet{
    $this->filters[] = ["OR"  => $this->extract_filters($filters)];
    return $this;
  }


  public function xor(...$filters): QuerySet{
    $this->filters[] = ["XOR"  => $this->extract_filters($filters)];
    return $this;
  }


  public function group_by(string ...$filters): QuerySet{
    if(array_key_exists("GROUP BY", $this->modifiers)){
      throw new \Exception("Multiple Group By!");
    }
    $this->modifiers["GROUP BY"] = $filters;
    return $this;
  }


  public function order_by(string ...$filters): QuerySet{
    if(array_key_exists("ORDER BY", $this->modifiers)){
      throw new \Exception("Multiple order By!");
    }
    $this->modifiers["ORDER BY"] = $filters;
    return $this;
  }



  public function all(): QuerySet{
    $this->filters = array();
    return $this;
  }

  /**
   * @todo:  Implement Relationships
   */

  /*public function union(): QuerySet{

  }*/






  public function count(): int{
    return $this->result->num_rows;
  }


  public function do(){
    list($query, $values) = $this->buildQuery();
    $db = Application::getDb();

    if(count($values) > 0 )
      $result = $db->query($query, ...$values);
    else
      $result = $db->query($query);

    $this->result = $result->fetch_result() ;
    return $this;
  }



  private function buildQuery(){

    $tableName = $this->model::getTableName();
    $query = "SELECT * FROM $tableName";
    $values = array();

    if(count($this->filters)> 0){
      list($whereClause, $values) = $this->buildWhereClause($this->filters);
      $query .= sprintf(" WHERE %s ", $whereClause);
    }

    if(count($this->modifiers)> 0){
      $modifiers = $this->buildModifiers($this->modifiers);
      $query .= sprintf(" %s", $modifiers);
    }

    return array($query, $values);
  }



  private function buildModifiers(array $modifiers){
   return implode(", ", array_map(function($v,$k){
      if(is_array($v)){
        return $k. " ".implode(', ',$v);
      } else {
        return $k. " ".$v;
      }
    }, $modifiers, array_keys($modifiers))
  );
  }

  private function buildWhereClause($filters){
    $where = "";
    $parameters = array();
    foreach($filters as $n => $filter){
      foreach($filter as $logic => $values){
        if($n != 0) $where .= sprintf(" %s ", $logic);
        if(is_int(array_key_first($values))){
          $atomic = $this->buildWhereClause($values);
          $where .= sprintf("(%s)", $atomic[0]);
        }
        else {
          $atomic = $this->buildAtomicWhereClause($values);
          $where .= $atomic[0];
        }
        $parameters = array_merge($parameters, $atomic[1]);

      }
    }

    return array($where, $parameters);
  }

  private function buildAtomicWhereClause($filters){

      $conditions = array();
      $values = array();

      array_walk($filters, function(mixed &$value, string $key) use (&$conditions, &$values){

        $lookup = $this->lookup($key);
        $column = $lookup["column"];
        $condition = $lookup["condition"];
        $map = $this->lookup_map[$condition];
        $values[$column] = ($map["rhs"])($value);
        $conditions[$column] = sprintf("%s %s ?", $column, $map["operator"]);  ;
      });

      $clause = implode(" AND ", $conditions);
      return array(0=>$clause, 1=> array_values($values));
  }


  public function lookup(string $value): array{
    $ismatch = preg_match('/(?<column>.+)__(?<condition>.+)/', $value, $match);
    return array("column"=>$ismatch ? $match["column"] : $value, "condition"=>$ismatch ? $match["condition"] : "equals");
}


}
