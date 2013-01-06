<?php

class Db_MongoDb_Adapter extends Db_DataBase {
	public $error;
	protected $_ndb;
	private $_cursor;
	private static $_instance  = array();

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

	public function connect($pConfig = 'default'){
		/*
		$tDB = Yaf_Registry::get('config')->db->$pConfig->toArray();
		$tDB['host']  = 'localhost';
		$tDB['port'] = '27017';
		$tDB['database'] = 'test';
		*/
		$tDB = Yaf_Registry::get('config')->db->$pConfig->toArray();
		$auth = '';
		if($tDB['user'] && $tDB['pass']){
			$auth = "{$tDB['user']}:{$tDB['pass']}@";
		}
		$host = "mongodb://{$auth}{$tDB['host']}:{$tDB['port']}";
		$this->_ndb = new Mongo($host);
		$ndb = $this->_ndb->selectDB($tDB['database']);
		return $ndb;
	}

	public function getQuery(){
		static $query;
		if(!$query){
			$query = new Db_MongoDb_Query($this->_cursor);
		}
		return $query;
	}

	public function getCollect(){
		static $collect;
		static $source;
		if($source != $this->_meta['source'] || !$query){
			$source = $this->_meta['source'];
			$collect = $this->_db->selectCollection($this->_meta['source']);
		}
		return $collect;
	}

	public function close() {
		$this->_ndb->close();
	} 

	public function query($cmd,$args = array()){
		if (is_array($cmd)) {
			return $this -> _db -> command($cmd);
		}
		return false;
	} 

	public function insert($value, $safe = true) {
		return $this->getCollect()->insert($value,array('safe' => true));
	} 

	public function remove($where, $options = array()) {
		$options += array('safe'=> true,'justOne' => false);
		return $this->getCollect()->remove($where,$options);
	} 

	public function update($data,$where,$options = array()){
		$options += array('multiple' => true,'safe' => true);
		//$data = preg_match('/^\$[\w]+/',key($data))?$data:array('$set'=>$data);
		return $this->getCollect()->update($where,$data,$options);
	}

	public function read($type,$conditions = array()){
		if(in_array($type, array('mapReduce','group'))){
			return $this->{$type}($conditions);
		}
		$type = $type."Select";
		return $this->{$type}($conditions);
	}

	public function firstSelect($where) {
		$select['_id'] = 0;
		$conditions = array();
		if($where['fields']) {
			$select = $this->getQuery()->fields($where['fields'])+$select;
			unset($where['fields']);
		}
		if($where['where']){
			$conditions = $where['where'];
			unset($where['where']);
		}
		$this->_cursor = $this->getCollect()->findOne($conditions,$select);
		if($this->_cursor) {
			$this->_data = $this->_cursor;
			$this->_cursor = '';
			return $this;
		}
		return false;
	} 

	public function allSelect($where) {
		$select['_id'] = 0;
		$conditions = array();
		if($where['fields']) {
			$select = $this->getQuery()->fields($where['fields'])+$select;
			unset($where['fields']);
		}
		if($where['where']){
			$conditions = $where['where'];
			unset($where['where']);
		}
		$this->_cursor = $this->getCollect()->find($conditions,$select);
		if(count($where) > 0){
			foreach ($where as $key => $value) {
				$this->getQuery()->{$key}($value);
			}
		}
		if($this->_cursor){
			$this->_data = iterator_to_array($this->getQuery()->getInstance());
			$this->_cursor = '';
			return $this;
		}
		return false;
	} 

	public function mapReduce($conditions) {
		$default = array('mapreduce' => $this->_meta['source'],
			'out' => 'sample' . mt_rand()
			);
		$conditions += $default;
		if($conditions['map'] && $conditions['reduce']){
			if (stripos($conditions['map'], 'function') && stripos($conditions['reduce'], 'function')) {
				$conditions['map']= new MongoCode($conditions['map']);
				$conditions['reduce'] = new MongoCode($conditions['reduce']);
			}else return false;
			if($this->query($options)){
				$source = $this->_meta['source'];
				$this->_meta['source'] = $options['out'];
				$this->selectall(array());
				$this->_meta['source'] = $source;
				return $this;
			}
		} 
		return false;
	}
	
	public function group($args = array()) {
		if($args['keys'] && $args['reduce'] && $args['initial']){
			if (stripos($args['reduce'], 'function') !== false) {
				$args['reduce'] = new MongoCode($args['reduce']);
			}else return false;
			if(is_string($args['keys']) && (stripos($args['keys'],'function') !== false)){
				$args['keys'] = new MongoCode($args['keys']);
			}
			$args['conditions'] = $args['conditions']?:array();
			extract($args);
			if($data = $this->getCollect()->group($keys,$initial,$reduce,$conditions)){
				$this->_data = $data;
				return $this;
			}
		}
		return false;  
	}

	public function autoIncrement($namespace,$collect = 'seq',$option = array()) {
		$option += array('init' => 1,
			'step' => 1,
			);
		$source = $this->_meta['source'];
		$this->_meta['source'] = $collect;
		$seq = $this->query(array(
					'findAndModify' => $collect,
					'query' => array('name' => $namespace),
					'update' => array('$inc' => array('ids' => $option['step'])),
					'new' => true,
				));
		if (isset($seq['value']['ids'])) {
			return $seq['value']['ids'];
		} 
		$this -> insert(array('name' => $namespace,'ids' => $option['init']));
		$this->_meta['source'] = $source;
		return $option['init'];
	}
} 

?>

