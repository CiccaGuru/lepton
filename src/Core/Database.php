<?php
namespace Lepton\Core;

class Database {

  protected $connection;
	protected $query;
  protected $show_errors = TRUE;
  protected $query_closed = TRUE;

	public function __construct($dbhost = 'localhost', $dbuser = 'root', $dbpass = '', $dbname = '', $charset = 'utf8') {
		$this->connection = new \mysqli($dbhost, $dbuser, $dbpass, $dbname);
		if ($this->connection->connect_error) {
			$this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
		}
		$this->connection->set_charset($charset);
	}

  public function query($query, &...$args) {
    if(!$this->query_closed){
      $this->query_closed = TRUE;
      $this->query->close();
    }
		if ($this->query = $this->connection->prepare($query)) {
      if(count($args)){
        $types = implode(array_map(array($this, '_gettype'), $args));
        $this->query->bind_param($types, ...$args);
      }

      $this->query->execute();
      if ($this->query->errno) {
        $this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
      }
      $this->query_closed = FALSE;
    } else {
      $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
    }
		return $this;
  }


  public function fetch_all($callback = null) {
    $params = array();
    $row = array();
    $meta = $this->query->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    $this->query->bind_result(...$params);

    $result = array();
    while ($this->query->fetch()) {
      $r = array();
      foreach ($row as $key => $val) {
        $r[$key] = $val;
      }
      if ($callback != null && is_callable($callback)) {
        $value = $callback($r);//call_user_func($callback, $r);
        if ($value == 'break') break;
      } else {
        $result[] = $r;
      }
    }
    $this->query_closed = TRUE;
    return $result;
	}

	public function fetch_array() {
    $params = array();
    $row = array();
    $meta = $this->query->result_metadata();
    while ($field = $meta->fetch_field()) {
      $params[] = &$row[$field->name];
    }
    $this->query->bind_result(...$params);
    $result = array();
		while ($this->query->fetch()) {
			foreach ($row as $key => $val) {
				$result[$key] = $val;
			}
		}
		return $result;
	}

  public function fetch_result(){
		return $this->query->get_result();
  }




	public function close() {
		return $this->connection->close();
	}

  public function num_rows() {
		$this->query->store_result();
		return $this->query->num_rows;
	}

	public function affected_rows() {
		return $this->query->affected_rows;
	}

  public function last_insert_id() {
    return $this->connection->insert_id;
  }

  public function error($error) {
    if ($this->show_errors) {
        die($error);
    }
  }

	private function _gettype($var) {
	    if (is_string($var)) return 's';
	    if (is_float($var)) return 'd';
	    if (is_int($var)) return 'i';
	    return 'b';
	}

}
?>