<?php
class Db_MySql_Result {
	public $query = NULL;

	function __construct($query){
		$this->query = $query;
	}

	public function fetch($type = 'ASSOC'){
        $type = strtoupper($type);
        return $this->query->fetch(self::_getFetchStyle($type));
    }

    public function fetchAll($type = 'ASSOC'){
        $type = strtoupper($type);
        $result = $this->query->fetchAll(self::_getFetchStyle($type));
        $this->free();
        return $result;
    }

	public function rowCount(){
		return $this->query->rowCount();
	}

	public function columnCount(){
		return $this->query->columnCount();
	} 

	public function data(){
		return $this->fetchAll();
	}

	protected static function _getFetchStyle($style){
        switch ($style) {
            case 'ASSOC':
                $style = PDO::FETCH_ASSOC;
                break;
            case 'BOTH':
                $style = PDO::FETCH_BOTH;
                break;
            case 'NUM':
                $style = PDO::FETCH_NUM;
                break;
            case 'OBJECT':
                $style = PDO::FETCH_OBJECT;
                break;
            default:
                $style = PDO::FETCH_ASSOC;
        }
        return $style;
    }

	function free(){
		$this->query = NULL;
	}
}