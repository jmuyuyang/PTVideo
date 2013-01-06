<?php
class Cache{
	protected static $_config = array();
	protected static $_cacheInstance = array();

	public static function config($config){
		$default = array(
			'default' =>array(
				'adapter' => 'Memcache',
				'host' => '127.0.0.1',
				'port' => '11211'
			)
		);
		self::$_config += $default;
	}

	public static function adapter($adapter){
		if(!self::$_cacheInstance[$adapter]){
			$config = self::$_config['default'];
			$cacheClass = 'Cache_'.$config['adapter'];
			$cache = new $cacheClass();
			$cache->connect($config);
			self::$_cacheInstance[$adapter] = $cache;
		}
		return self::$_cacheInstance[$adapter];
	}

	public static function set($key,$value,$expire = 0,$adapter = 'default'){
		return self::adapter($adapter)->set($key,$value,$expire);
	}

	public static function get($key,$adapter = 'default'){
		return self::adapter($adapter)->get($key);
	}

	public static function inc($key,$step,$adapter = 'default'){
		return self::adapter($adapter)->inc($key,$step);
	}

	public static function delete($key,$adapter = 'default'){
		return self::adapter($adapter)->delete($key);
	}

	public static function replace($key,$value,$adapter = 'default'){
		return self::adapter($adapter)->replace($key,$value);
	}
}