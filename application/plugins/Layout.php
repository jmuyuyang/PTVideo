<?php
class LayoutPlugin extends Yaf_Plugin_Abstract{

	public $engine;
	public $enable = false;
	private $_layoutDir = NULL;
	private $_layoutFile = "layout/layout.phtml";
	private $_layoutVars;

	public function __set($name,$val){
		$this->_layoutVars[$name] = $val;
	}

	public function enable(){
		if($this->_layoutDir){
			$this->enable = true;
		}
	}

	public function preDispatch(Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
		if($request->isXmlHttpRequest()){
			Yaf_Dispatcher::getInstance()->disableView();
			return;
		}
		$module = $request->getModuleName();
		if($module == "Index") $this->_initLayout($module);
	}

	public function postDispatch (Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        if($this->enable){
        	$this->_startLayout($response);
        }
    }

    private function _initLayout($module){
		$pre_dir = "";
		if($module != "Index") $pre_dir = "/module/".$module;
		$this->_layoutDir = sprintf("%s/application/%sviews",APPLICATION_PATH,$pre_dir);
    }

    private function _engine(){ 	
    	$this->engine = $this->engine?:new Yaf_View_Simple($this->_layoutDir);
    	return $this->engine;
    }

    private function _startLayout($response){
        $body = $response->getBody();
        $response->clearBody();
        $layout = $this->_engine();
        $layout->content = $body;
        $layout->assign($this->_layoutVars);
        $response->setBody($layout->render($this->_layoutFile));
    }
}