<?php
class MessageModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_video_msg',
		'key' => 'mid'
	);

	function send($msgInfo,$extendInfo){
		$msgInfo['extends'] = serialize($extendInfo);
		$msgInfo['create_time'] = time();
		return $this->insert($msgInfo);
	}

	function get($uid,$limit,$offset){
		$msg = $this->find("all",array(
			"where" => array("send_user" => $uid),
			"fields" => array("content","create_user","create_username","extends","create_time"),
			"limit" => $limit,
			"offset" => $offset
		));
		return $msg?$msg->data():array();
	}

	function setRead($uid){
		$setRead = $this->update(array('has_read' => 1),array("send_user" => $uid,"has_read" => 0));
		$updateNewMsg = $this->update(array("new_msg" => 0),array("uid" => $uid));
		if($updateNewMsg && $setRead){
			return true;
		} 
		return false;
	}
}