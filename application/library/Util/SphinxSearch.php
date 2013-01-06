<?php
/*
* @yuyang
*/
class Util_SphinxSearch{
	protected $_sphinx;
	protected static $_host = "localhost";
	protected static $_port = 9312;

	public static function setServer($host,$port){
		self::$_host = $host;
		self::$_port = $port;
	}

	public function __construct(){
		$this->_sphinx = new SphinxClient();
		$this->_sphinx->setServer(self::$_host,self::$_port);
	}

	public function matchMode($mode = SPH_MATCH_ALL){
		$this->_sphinx->setMatchMode($mode);
		return $this;
	}
	
	
	public function sort($mode,$attr = NULL){
		$this->_sphinx->setSortMode($mode,$attr);
		return $this;
	}

	public function maxTime($time){
		$this->_sphinx->setMaxQueryTime($time);
		return $this;
	}

	public function group($mode,$attr = NULL){
		$this->_sphinx->setGroupBy($attr,$mode,"@group desc");
		return $this;
	}

	public function idRange($min,$max){
		$this->_sphinx->setIdRange($min,$max);
		return $this;
	}

	public function limit($offset,$limit,$max_matches = 50){
		$this->_sphinx->setLimits($offset,$limit,$max_matches);
		return $this;
	}

	public function filter($attr,$val,$exclude = false){
		$this->_sphinx->setFilter($attr,$val,$exclude);
		return $this;
	}

	public function search($query,$index = NULL){
		$results = array();
		$docInfo = $this->_sphinx->query($query,$index);
		$total = $docInfo['total'];
		$time = $docInfo['time'];
		if($total){
			foreach($docInfo['matches'] as $id => $attr){
				$result[$id] = $attr['attrs'];
			}
		}
		return array($total,$result,$time);
	}
}




?>