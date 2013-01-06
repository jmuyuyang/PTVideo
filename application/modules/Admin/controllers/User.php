<?php
class UserController extends Controller{
	public $user;
	public $session;
	
	function init(){
		parent::init();
		$this->user = $this->loadModel('AdminUser');
		$this->session = Yaf_Session::getInstance();
	}

	function loginAction(){
		$name = $this->request->data['name'];
		$pass = $this->request->data['pass'];
		if($name && $pass){
			$shell = $this->user->login($name,$pass);
			if($shell){
				$this->session->auid = $shell[0];
				$this->session->ashell = $shell[1];
				$this->redirect('/admin');
			}
			else $this->sendMsg('/admin/login','登陆失败.用户名或密码输入错误');
		}
	}

	function logOutAction(){
		session_destroy();
		echo "<script type='text/javascript'>parent.location.href='/admin/login'</script>";
		Yaf_Dispatcher::getInstance()->disableView();
	}
}