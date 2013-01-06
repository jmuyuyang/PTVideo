<?php
class Db_DataBase{
	public $_db;
	protected $_meta;
	protected static $_dbConfig;
	protected $_data = array();

	public function __construct($connection){
		$this->_db = $this->connect($connection);
	}
	
	public function init($config){
		$this->_meta = $config;
	}

	public function connect(){

	}

	function create($data){
		$this->_data = $data;
	}
	
	function save($type = 'insert'){
        if(!empty($this->_data)) {
            $data = $this->_data;
            $this->_data = array();
        }else{
            $this->error = 'Invalid Data To Save';
            return false;
        }
      	if($type == 'update'){
      		if(!array_key_exists($this->_meta['key'], $data)){
      			$this->error = 'key is not exists';
      			return false;
      		}
      		$where = array($this->_meta['key'] => $data[$this->_meta['key']]);
      		return $this->update($data,$where);
      	}
		else {
			return $this->$type($data);
		}
	}

	function update(){

	}

	function insert(){

	}

	function read(){

	}

	public static function loadConfig($item){
		if(!self::$_dbConfig){
			self::$_dbConfig = include LITHIUM_APP_PATH.'/config/dbConfig.php';
		}
		return self::$_dbConfig[$item];
	}

	function data($key = NULL){
		if($key){
			return $this->_data[$key];
		}
		return $this->_data;
	}

	public function __destruct(){
		if($this->_db){
			$this->close();
		}
	}
}