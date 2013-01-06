<?php
class Cache_Memcache{
	protected $_link;
	protected $_expire = 864000;

	function connect($config){
		if($config['server']){
			$this->_link = new Memcached();
			$this->_link->addServers($config['server']);
		}else{
			$this->_link = new Memcache();
			$this->_link->connect($config['host'],$config['port']); 
		}
	}

	function set($key,$value = '',$expire = 0){
		$expire = ($expire > 0)?$expire:$this->_expire;
		if(is_array($key)){
			return $this->_link->setMulti($key,time()+$expire);
		}
		else return $this->_link->set($key,$value,time()+$expire);
	}

	function get($key){
		if(is_array($key)){
			$value = $this->_link->getMulit($key);
		}
		$value = $this->_link->get($key);
		return $value;
	}

	function delete($key){
		return $this->_link->delete($key);
	}

	function replace($key,$value,$expire = 0){
		$expire = ($expire > 0)?$expire:$this->_expire;
		return $this->_link->replace($key,$value,time()+$expire);
	}

	function incr($key,$step){
		return $this->_link->increment($key,$step);
	}
}
