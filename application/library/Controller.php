<?php
class Controller extends Yaf_Controller_Abstract{
	
	public $request;
	public $response;
	public $classConfig;
	public $app_config;

	protected $_userInfo;

	public function init(){
		$this->request = $this->getRequest();
		$this->response = $this->getResponse();
		$this->app_config = Yaf_Registry::get("config")->application;
		$this->auth();
	}

	public function loadModel($model,$params = array()){
		$modelName = $model."Model";
		static $modelConfig = array();
		if($modelConfig[$model]) return $modelConfig[$model];
		$entity = new $modelName();
		$modelConfig[$model] = $entity;
		return $entity;
	}

	public function loadClassConfig(){
		if(!$this->classConfig){
			include $this->app_config->directory."/config/class_config.php";
			$this->classConfig['classes'] = $puclass+$prclass;
			$this->classConfig['classInfo'] = $classInfo;
		}
	}

	public function sendMsg($msg = '操作已成功！',$url = null,$pause = 0){
		$this->getView()->display($this->getView()->getScriptPath().'/common/msg.phtml',array('url' => $url,'msg' => $msg));
		if($pause) exit();
	}	

	protected function pagenator($sum,$limit,$page){
		$pages = ceil($sum/$limit);
		$this->getView()->assign(compact("page","pages"));
		return $limit*($page-1);
	}

	protected function setLayout(){
		$this->loadClassConfig();
		$layout = Yaf_Registry::get("layout");
		$layout->enable();
		$this->getView()->assign("classInfo",$this->classConfig['classInfo']);
		$this->getView()->assign("classList",$this->classConfig['classes'][0]);
		$this->getView()->assign("userInfo",$this->_userInfo);
	}

	protected function auth(){
		if($url = $this->request->getParam("redirect")){
			if($this->request->getModuleName() == "Admin"){
				echo "<script type='text/javascript'>parent.location.href='/admin".$url."'</script>";
			}else{
				$this->redirect($url);
			}
			return;
		}
		$this->_userInfo = $this->request->userInfo;
	}
}