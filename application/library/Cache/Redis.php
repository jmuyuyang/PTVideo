<?php
class Cache_Redis{
	protected $_link;

	function connect($config){
		$this->_link = new Redis();
		$this->_link->connect($config['host'],$config['port']);
	}

	function set($key,$value,$expire = 0){
		if(is_array($key)){
			$this->_link->mset($key);
		}else{
			if(is_array($value)){
				$this->_link->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
			}
			if($expire > 0){
				$this->_link->setex($key,$expire,$value);
			}else{
				$this->_link->set($key,$value);
			}
		}
	}

	function get($key){
		if(is_array($key)){
			$value = $this->_link->mget($key);
		}else {
			$value = $this->_link->get($key);
		}
		return $value;
	}

	function hSet($key,$value){
		if(is_array($value)){
			return $this->_link->hSet($key,$value);
		}
		return false;
	}

	function hGet($key,$item = NULL){
		if($item){
			return $this->_link->hGet($key,$item);
		}
		return $this->_link->hGetAll($key);
	}

	function inc($key,$step){
		if($step == 1) $this->_link->incr($key);
		else $this->_link->incrBy($key,$step);
	}
}