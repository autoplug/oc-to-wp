<?php
class db {
	private $link;
	private $result;
	private $sql;

	public function __construct($hostname, $username, $password, $database) {
		if (!$this->link = mysqli_connect($hostname, $username, $password, $database)) {
			echo('Error: Could not make a database link using ');
		}
		$this->link->set_charset("utf8");
		$this->link->query("SET SQL_MODE = ''");
	}

	public function query($sql) {
		$query = $this->link->query($sql);
		$this->sql = $sql;

		if (!$this->link->errno){
			if (isset($query->num_rows)) {
				$data = array();

				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}

				$result = new stdClass();
				$result->num_rows = $query->num_rows;
				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;

				unset($data);

				$query->close();

				$this->result = $result;
				return $result;
			} else{
				return true;
			}
		} else {
			throw new ErrorException('Error: ' . $this->link->error . '<br />Error No: ' . $this->link->errno . '<br />' . $sql);
			exit();
		}
	}

	public function fetch($index = ""){
		$result = [];
		foreach ($this->result->rows as $row) {
			if($index=="")
				$result[] = $row[key($row)];
			elseif(isset($row[$index]))
				$result[] = $row[$index];
		}
		
		return $result;
	}

	public function escape($value) {
		if ($this->link) {
			return mysqli_real_escape_string($this->link,$value);
		}
	}

	public function count() {
		if ($this->link) {
			return mysqli_affected_rows($this->link);
		}
	}

	public function lastId() {
		if ($this->link) {
			return mysqli_insert_id($this->link);
		}
	}

	public function __destruct() {
		if ($this->link) {
			mysqli_close($this->link);
		}
	}
}