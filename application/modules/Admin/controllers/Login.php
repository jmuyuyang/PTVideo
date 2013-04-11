<?php
class LoginController extends Controller{
	
	public $user;
	public $session;
	
	function init(){
		parent::init();
		$this->user = $this->loadModel('User');
		$this->session = Yaf_Session::getInstance();
	}

	function loginAction(){
		$name = $this->request->data['name'];
		$pass = $this->request->data['pass'];
		if($name && $pass){
			$shell = $this->user->login($name,$pass,1);
			if($shell){
				$this->session->auid = $shell[0];
				$this->session->ashell = $shell[1];
				$this->redirect('/admin');
			}
			else $this->sendMsg('登陆失败.用户名或密码输入错误');
		}
	}
}