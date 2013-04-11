<?php
class InviteController extends Controller{
	function indexAction(){
		$uid = $this->_userInfo['uid'];
		$invite = $this->_loadModel("Invite");
		$inviteList = $invite->getList($uid);
		$this->getView()->assign("inviteList",$inviteList);
	}

	function inviteAction(){
		$invite_user = $this->_userInfo['uid'];
		$invite = $this->loadModel("Invite");
		$hash = md5(mt_rand(1,10000).$this->_userInfo['username'].time().$this->_userInfo['password']);
		$add = $invite->add(compact('invite_user','hash'));
		if($add) $this->response->setBody(json_encode(array('inviteHash' => $hash)));
		else $this->response->setBody(json_encode(array('error' => 1)));
	}

	function infoAction(){
		$id = $this->request->params['id'];
		$invite = $this->loadModel("Invite");
		$user = $invite->getUsed($id);
		if(!$user) $this->response->setBody(json_encode(array('error' => 1,"msg" => $invite->errors())));
		$user['addtime'] = strftime("%Y-%m-%d",$user['addtime']);
		$this->response->setBody(json_encode($user));
	}

}