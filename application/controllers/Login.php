<?php
class LoginController extends Controller{

	public function init(){
		parent::init();
		$this->user = $this->loadModel('User');
		$this->cookie = Util_Cookie::getInstance();
	}

	function loginAction(){
		$name = $this->request->data['name'];
		$password = $this->request->data['userpass'];
		if($name && $password){
			$shell = $this->user->login($name,$password);
			if($shell){
				$this->cookie->huid = $shell[0];
				$this->cookie->hshell = $shell[1];
				$url = $this->request->query['url']?:"/";
				$this->redirect($url);
			}
			$this->getView()->assign("errors","登陆失败.用户名或密码输入错误");
		}
	}

	function signupAction(){
		$username = $this->request->data['name'];
		$password = $this->request->data['userpass'];
		$email = $this->request->data['mail'];
		$invitenumber = $this->request->data['invitenumber'];
		if($username && $password && $email){
			$userInfo = compact('username','password','email');
			$info = $this->loadModel("Invite")->check($invitenumber);
			if($info['errors']) $this->sendMsg($info['errors'],"/signup");
			$userInfo['invite_user'] = $info['invite_user'];
 			$signId = $this->user->add($userInfo);
			if($signId) {
				$this->loadModel("Invite")->setUsed($info['id'],$signId);
				$this->_sendNotify($signId,"welcome to PTVideo");
				$this->getView()->assign('注册成功');
			}
			else $this->getView()->assign('注册失败 '.$this->user->errors());	
		}
	}

	function checkAction(){
		$type = $this->request->query['t'];
		$val = $this->request->query['v'];
		if($type == "user") $where = array("username" => $val);
		if($type == "email") $where = array("email" => $val);
		$existed = $this->user->userCheck($where);
		$this->response->setBody(json_encode(array("existed" => $existed)));
	}

	private function _sendNotify($send_user,$content){
		$add = $this->loadModel("Message")->add($send_user,0,array("content" => $content));
		$update = $this->loadModel("User")->updateNewMsg($send_user);
	}
}
