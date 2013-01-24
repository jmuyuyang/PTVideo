<?php 
class UserController extends Controller{
	public $cookie;
	public $user;

	public function init(){
		parent::init();
		$this->user = $this->loadModel('HomeUser');
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
			}else $this->sendMsg('/login','登陆失败.用户名或密码输入错误');
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
			if($info['errors']) $this->sendMsg("/signup",$info['errors']);
			$userInfo['invite_user'] = $info['invite_user'];
 			$signId = $this->user->add($userInfo);
			if($signId) {
				$this->loadModel("Invite")->setUsed($info['id'],$signId);
				$this->_sendNotify($signId,"welcome to PTVideo");
				$this->sendMsg('/','注册成功',0);
			}
			else $this->sendMsg('/signup','注册失败 '.$this->user->errors(),0);	
		}
	}

	function logOutAction(){
		unset($this->cookie->huid);
		unset($this->cookie->hshell);
		$this->redirect('/login');
		Yaf_Dispatcher::getInstance()->disableView();
	}

	function checkAction(){
		$type = $this->request->query['t'];
		$val = $this->request->query['v'];
		$method = $type."Check";
		$existed = $this->user->{$method}($val);
		$this->response->setBody(json_encode(array("existed" => $existed)));
	}

	private function _sendNotify($send_user,$content){
		$add = $this->loadModel("Message")->send($send_user,0,array("content" => $content));
		$update = $this->loadModel("HomeUser")->update(array("new_msg" => array("inc" => 1)),array("uid" => $send_user));
	}
}
?>