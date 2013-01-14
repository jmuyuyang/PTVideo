<?php 
class ManageController extends Controller{

	function init(){
		parent::init();
		$this->setLayout();
	}
	
	function indexAction(){
		if(!$this->_userInfo['is_admin']) {
			$this->sendMsg('/',"你没有查看权限");
			exit();
		}
		$search = $this->request->params['search'];
		$is_admin = $this->request->query['type'];
		$page = $this->request->query['page']?:1;
		$user = $this->loadModel('HomeUser');
		$pages = ceil(($user->find("all")->rowCount()) / 20);
		$offset = ($page-1)*20;
		$uri = $this->_getRequestUri();
		$userList = $user->getList($search,$is_admin,20,$offset);
		$inviteList = $this->loadModel("Invite")->getList($this->_userInfo['uid']);
		$this->getView()->assign(compact("pages","page","uri"));
		$this->getView()->assign(compact("userList","inviteList"));
	}

	function powerAction(){
		if(!$this->_userInfo['is_admin']) $this->response->setBody(json_encode(array('error' => 1)));
		$uid = $this->request->params['uid'];
		$power = (int)$this->request->query['set'];
		$user = $this->loadModel("HomeUser");
		$set = $user->setPower($uid,$power);
		if($set) $this->response->setBody(json_encode(array('error' => 0)));
		else $this->response->setBody(json_encode(array('error' => 1)));
	}

	function inviteAction(){
		if(!$this->_userInfo['is_admin']) $this->response->setBody(json_encode(array('error' => 1)));
		$invite_user = $this->_userInfo['uid'];
		$invite = $this->loadModel("Invite");
		$hash = md5(mt_rand(1,10000).$this->_userInfo['username'].time().$this->_userInfo['password']);
		$add = $invite->add(compact('invite_user','hash'));
		if($add) $this->response->setBody(json_encode(array('inviteHash' => $hash)));
		else $this->response->setBody(json_encode(array('error' => 1)));
	}

	function inviteUserAction(){
		if(!$this->_userInfo['is_admin']) $this->response->setBody(json_encode(array("error" => 1,"msg" => "权限不够")));
		$id = $this->request->params['id'];
		$invite = $this->loadModel("Invite");
		$user = $invite->getUsed($id);
		if(!$user) $this->response->setBody(json_encode(array('error' => 1,"msg" => $invite->errors())));
		$user['addtime'] = strftime("%Y-%m-%d",$user['addtime']);
		$this->response->setBody(json_encode($user));
	}

	function _getRequestUri(){
		$uri = $this->request->getServer("REQUEST_URI");
		if($pos = strpos($uri, "page")) $uri=substr($uri,0,$pos-1); 
		if(!strpos($uri,"?")) $uri.="?";
		else $uri.="&";
		return $uri;
	}
} 