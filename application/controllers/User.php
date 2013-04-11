<?php 
class UserController extends Controller{

	public function init(){
		parent::init();
		$this->setLayout();
	}

	function profile(){
		if($data = $this->request->data){
			$modify = $this->loadModel("User")->modifyProfile($this->_userInfo['uid'],$data);
			//$this->sendMsg(null,'更新成功',0);
		}
		$this->getView()->assign("profile",$this->_userInfo);
	}

	function logOutAction(){
		$cookie = Util_Cookie::getInstance();
		unset($cookie->huid);
		unset($cookie->hshell);
		$this->redirect('/login');
		Yaf_Dispatcher::getInstance()->disableView();
	}
}
?>