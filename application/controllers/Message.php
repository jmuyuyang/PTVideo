<?php
class MessageController extends Controller{

	function init(){
		parent::init();
		$this->setLayout();
	}

	function indexAction(){
		$update = $this->request->query['update'];
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
		if($update) $msg->setRead($uid);
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
		$msg = $this->loadModel("Message");
		$msg->send($send_user,$create_user,array("content" => $content));
	}
}