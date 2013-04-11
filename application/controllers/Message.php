<?php
class MessageController extends Controller{

	function init(){
		parent::init();
		$this->setLayout();
	}

	function indexAction(){
		$uid = $this->_userInfo["uid"];
		$msg = $this->loadModel("Message");
		$messages = $msg->get($uid);
		$msgType = array();
		foreach($messages as $message){
			$msgType[$message['id']] = $message;
			$data = $msg->getContent($message['id']);
			$msgType[$message['id']]['content'] = $data['content'];
			$msgType[$message['id']]['create_time'] = $data['create_time']; 
		}
		$this->_resetNewMsg($uid);
		$this->getView()->assign("messages",$msgType);
	}

	function listAction(){
		$mid = $this->request->params['mid'];
		$msgList = $this->loadModel("Message")->getContent($mid,"all");
		unset($msgList[0]);
		$this->response->setBody(json_encode($msgList));
	}

	function addAction(){
		$send_user = $this->request->params['sid'];
		$create_user = $this->_userInfo['uid'];
		$content = $this->request->data['content'];
		$add = $this->_sendMsg($send_user,$create_user,$content);
		if($add) $this->response->setBody(json_encode(array("operation" => "successful")));
		else $this->response->setBody(json_encode(array("operation" => "failed")));
	}

	private function _sendMsg($send_user,$create_user,$content){
		$add = $this->loadModel("Message")->send($send_user,$create_user,array("content" => $content));
		$update = $this->loadModel("User")->updateNewMsg($send_user);
		if($add && $update) return true;
		return false;
	}

	private function _resetNewMsg($uid){
		if($this->_userInfo['new_msg']){
			$this->loadModel("User")->resetNewMsg($uid);
		}
	}
}