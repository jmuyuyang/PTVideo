<?php
class Db_MongoDb_Query{

	private $_query;

	function __construct($query){
		$this->_query = $query;
	}

	function getInstance(){
		return $this->_query;
	}

	public function fields($fields){
		foreach ($fields as $field) {
			$select[$field] = 1;
		}
		return $select; 
	}

	public function limit($size){
		if($this->_query){
			$this->_query = $this->_query->limit($size);
		}
		return $this;
	}

	public function offset($offset){
		if($this->_query){
			$this->_query = $this->_query->skip($offset);
		}
		return $this;
	}

	public function order($order){
		if($this->_query){
			$this->_query = $this->_query->sort($order);
		}
		return $this;
	}
}