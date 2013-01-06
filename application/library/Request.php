<?php

class Request extends Yaf_Request_Http{
	public $data = null;
	public $query = null;
	public $params = null;
	public $userInfo = array();

	public function __construct(){
		parent::__construct();
		$this->setPost();
		$this->setQuery();
		$this->setParams();
	}

	public function setCurrentUser($userInfo){
		$this->userInfo = $userInfo;
	}

	public function setPost(){
		if($this->data){
			return $this->data;
		}
		$this->data = $this->filter_params(parent::getPost());
	}

	public function setQuery(){
		if($this->query){
			return $this->query;
		}
		$this->query = $this->filter_params($this->getQuery());
	}

	public function setParams(){
		if($this->params){
			return $this->params;
		}
		$this->params = $this->filter_params(parent::getParams());
	}

	public function filter_params($params){
		if(!empty($params)){
			array_walk_recursive($params, function (&$item,$key){
				return htmlspecialchars($itme);			
			});
		}
		return $params;
	}			
}