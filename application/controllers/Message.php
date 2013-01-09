<?php
class MessageController extends Controller{

	function get(){
		$update = $this->request->query['update'];
		$uid = $this->_userInfo["uid"];
		$msg = $this->loadModel("Message");
		$msgData = $msg->get($uid);
		if($update) $msg->setRead($uid);
		$this->getView()->assign("msgData",$msgData);
	}
}