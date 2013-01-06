<?php
class Db_MySql_Query {
	public $order_by = NULL;
	public $where = '';
	public $limit = NULL;
	public $offset = NULL;
	public $filter;
	public $source;
	public $fields = '';
	public $alias = array();
	public $operator = array(
		'lt' => ' < ',
		'gt' => ' > ',
		'lte' => ' <= ',
		'gte' => ' >= ',
		'like' => ' LIKE ',
		'inc' => '+',
		'notLike'=> ' NOT LIKE ',
		'in' => ' IN '
 	);

	public $join_conditions = array();

	public function limit($count){
		$this->limit = (int)$count;
		return $this;
	}

	public function source($source){
		$this->source = $source;
	}

	public function fields($fields){
		if(is_array($fields)){
			$this->fields = join(",",$fields);
		}
		return $this;
	}

	public function alias($alias){
		foreach($alias as $table => $name){
			$this->alias[$table] = $name;
		}
		return $this;
	}

	public function whereAnd($conditions){
		return $this->where($conditions,'and');
	}

	public function whereOr($conditions){
		return $this->where($conditions,'or');
	}

	public function where($conditions,$type = 'and'){
		$this->where .= $this->_format($conditions,' '.$type.' ');
		return $this;
	}

	public function filter($val,$like = false){
		$filter = $this->filter;
		if(is_array($val)){
			return $val = array_map($filter,$val);
		}	
		return $filter($val,$like);
	}

	public function order($order){
		if(is_array($order)){
			foreach($order as $field => $orderBy){
				$this->_orderBy($field,$orderBy);
			}
		}
		return $this;
	}

	public function offset($count){
		$this->offset = (int)$count;
		return $this;
	}

  	public function innerJoin($join)
    {
        $this->_join("INNER", $join['table'], $join['pKey'], $join['fKey']);
        if($join['where']) $this->whereAnd($join['where']);
        if($join['fields']) $this->fields($join['fields']);
        return $this;
    }

    public function leftJoin($join)
    {
        $this->_join("LEFT", $join['table'], $join['pKey'], $join['fKey']);
        if($join['where']) $this->where($join['where']);
        if($join['fields']) $this->fields($join['fields']);
        return $this;
    }

    private function _join($join_type, $join_table, $pKey, $fKey)
    {
    	$pSource = $this->source;
    	$pSource = $this->alias[$pSource]?:$this->source;
       	if($this->alias[$join_table]){
       		$fSource = $this->alias[$join_table];
       		$join_table = "`".$join_table."` as ".$this->alias[$join_table];
       	}else $fSource = $join_table;
        $this->join_conditions[] = "{$join_type} JOIN {$join_table} ON ({$pSource}.{$pKey} = {$fSource}.{$fKey})"; 
    }

    private function _format($data,$salt=','){
		$formatData = array();
		foreach($data as $key => $val){
			if(is_array($val)){
				if($val[0]) $operator = 'in';
				else{
					$operator = key($val);
					if($this->operator[$operator]){
						$val = current($val);
					}
				}
				$formatData[$key] = $this->operator($operator,$key,$val);
				continue;
			}
			$formatData[$key] = $key."=".$this->filter($val);
		}
		return join($salt,$formatData);
	}

	public function operator($operator,$field,$val){
		$like = ($operator == 'like' || $operator == 'notLike')?true:false;
		if($operator == 'inc') $field = $field."=".$field;
		if($operator == 'in') $val = "(".join(',',$this->filter($val)).")"; 
		else $val = $this->filter($val,$like);
		return $field.$this->operator[$operator].$val;
	}

	private function _orderBy($field,$order){
		$order = $order==1?"ASC":"DESC";
    	$this->order_by = (null === $this->order_by) 
            ? "{$field} ${order}" 
            : $this->order_by . ", {$field} ${order}";
	}

	function select(){
		$source = $this->alias[$this->source]?"`".$this->source."` as ".$this->alias[$this->source]:"`".$this->source."`";
		$fields = $this->fields ?"{$this->fields}":'*';
		$this->fields = '';
		$query = "SELECT {$fields} from {$source}";
		if (!empty($this->join_conditions)){
            $query .= " " . implode(" ", $this->join_conditions);
			$this->join_conditions = array();
		}
        if (!empty($this->where)){
            $query .= " WHERE {$this->where}";
			$this->where = '';
		}
        if (isset($this->order_by)){
            $query .= " ORDER BY {$this->order_by}";
			$this->order_by = NULL;
		}
        if (isset($this->limit)){
        	if(isset($this->offset)){
        		$query .= " LIMIT {$this->offset},{$this->limit}";
        		$this->offset = NULL;
        	}else{
        		$query .= " LIMIT {$this->limit}";
        	}
			$this->limit = NULL;
		}
		$this->alias = array();
        return $query;
	}

	function insert($data,$type){
		foreach($data as $key => $val){
			$fields = isset($fields)?$fields."`,`".$key:$key;
			$tVal = isset($tVal)?$tVal.",".$this->filter($val):$this->filter($val);
		}
		return "{$type} INTO {$this->source} (`{$fields}`) VALUES ({$tVal})";
	}

	function update($data,$conditions = array()){
		if(count($conditions) > 0) $where = $this->_format($conditions,' and ');
		return "UPDATE {$this->source} SET {$this->_format($data)} WHERE {$where}";
	}

	function delete($conditions){
		return "DELETE FROM {$this->source} where {$this->_format($conditions,' and ')}";
	}
}