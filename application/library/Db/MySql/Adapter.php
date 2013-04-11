<?php
class Db_MySql_Adapter extends Db_DataBase {	
	public $error;
	private $_query;
	
	public function init($config){
		parent::init($config);
		$this->setFilter();
	}

	function __get($name){
		$data = $this->data();
		if(array_key_exists($name,$data)){
			return $data[$name];
		}
		return false;
	}

	function __set($key,$val){
		$data = $this->data();
		if(array_key_exists($key,$data)){
			$this->_data[$key] = $val;
		}
		return;
	}

	function setFilter(){
		if($this->_meta['filter'] && is_callable($this->_meta['filter'])){
			$this->getQuery()->filter = $this->_meta['filter'];
		}
		else $this->getQuery()->filter = $this->filter();
	}

	public function connect($pConfig = 'default'){
		$tDB = Yaf_Registry::get('config')->db->$pConfig->toArray();
		$dsn = sprintf("mysql:host=%s;port=%s;dbname=%s", $tDB['host'], $tDB['port'], $tDB['database']);
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		try{
			$db = new PDO($dsn, $tDB['user'], $tDB['pass'],$options);
		}catch (PDOException $e){
			throw new Exception($e->getMessage());
			return false;
		}		
		return $db;
	}

	public function filter(){
		$self = $this;
		$filter = function ($val,$like = false) use ($self){
			if(get_magic_quotes_gpc()){
				$val = stripslashes($val);
			}
			if(!is_numeric($val)){
				$val = $self->_db->quote($val);
			}
			if ($like === false){
				$val = str_replace(array('%'), array('\\%'), $val);
			}
			return $val;
		};
		return $filter;	
	}

	function getQuery(){
		static $query;
		if(!$query){
			$query = new Db_MySql_Query();
		}
		if($this->_meta['source']) $query->source($this->_meta['source']);
		return $query;
	}
	
	function exec($sql){
		try{
			$this->_db->exec($sql);
			return true;
		} catch (PDOException $e){
			$errors = $this->_db->errorInfo();
			$this->error = $errors[2];
			throw new Exception($errors[2]);
			return false;
		}
	}

	function query($sql,$args = array()){
		if(!empty($args)){
			$this->_query = $this->_db->prepare($sql);
			$this->_query->execute($args);
		} else{
			$this->_query = $this->_db->query($sql);
		}
		if(!$this->_query instanceof PDOStatement){
			$errors = $this->_db->errorInfo();
			throw new Exception($errors[2]);
		}
		return new Db_MySql_Result($this->_query); 
	}

	function read($type,$conditions = array()){
		$query = $this->getQuery();
		foreach($conditions as $item => $val){
			$query = $query->{$item}($val);
		}
		$sql = $query->select();
		$result = $this->query($sql);
		if($this->_query && $this->_query->rowCount() > 0){
			if($type == 'first') {
				$this->_data = $result->fetch();
				return $this;
			}
			return $result;
		}
		return false;
	}

	function insert($data,$options = array()){
		$sql = $this->getQuery()->insert($data,'INSERT');
		if(!$this->exec($sql)) return false;
		$id = $this->_db->lastInsertId();
		return $id?:true;
	}

	function replace($data,$options = array()){
		$sql = $this->getQuery()->insert($data,'REPLACE');
		return $this->exec($sql);
	}

	function remove($where,$options = array()){
		$sql = $this->getQuery()->delete($where);
		return $this->exec($sql);
	}

	function update($data,$conditions,$options = array()){
		$sql = $this->getQuery()->update($data,$conditions);
		return $this->exec($sql);	
	}

	function autoIncrement($namespace,$source = 'seq',$option = array()){
	}

	function close(){
		if($this->_db){
			unset($this->_db);
		}
	}
}
