<?php

class Util_Cookie implements Iterator,ArrayAccess,Countable{
	
	private $_valid = false;

	public static $config = array(
		'expires' => 3600,
		'path' => '/',
		'domain' => null,
		'secure' => false,
		'httponly' => false,
		'encode' => true
	);

	private static $_instance;

	public static function getInstance(){
		if(!self::$_instance){
			self::$_instance  = new self();
		}
		return self::$_instance;
	}

	public static function config($config){
		self::$config = $config + self::$config;
	}

	public function __set($name,$value){
		self::set($name,$value);
	}

	public function __get($name){
		return self::get($name);
	}

	public function __unset($name){
		self::del($name);
	}

	public static function set($name, $value = null) {
		if (empty($name)) return false;
		$config = self::$config;
		($config['encode'] && $value) && $value = base64_encode($value);
		setcookie($name, $value, time()+$config['expires'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
		return true;
	}

	public static function get($name){
		if(self::exist($name)){
			$value = $_COOKIE[$name];
			(self::$config['encode'] && $value) && $value = base64_decode($value);
			return $value;
		}
		return false;
	}

	public static function del($name){
		if(self::exist($name)){
			self::set($name,'');
			unset($_COOKIE[$name]);
		}
		return false;
	}

	public static function exist($name){
		return isset($_COOKIE[$name]);
	}

	public function offsetSet($name,$value){
		return self::set($name,$value);
	}

	public function offsetGet($name){
		if($val = self::get($name)){
			return $val;
		}
		return false;
	}

	public function offsetUnset($name){
		return self::del($name);
	}

	public function offsetExists($name){
		return self::exist($name);
	}

	public function key(){
		return key($_COOKIE);
	}

	public function rewind(){
		$this->_valid = (false !== reset($_COOKIE));
	}

	public function current(){
		$val = current($_COOKIE);
		return self::$config['encode'] ? base64_decode($val) : $val;
	}

	public function next(){
		$this->_valid = (false !== next($_COOKIE));
	}

	public function valid(){
		return $this->_valid;
	}

	public function count(){
		return count($_COOKIE);
	}
}