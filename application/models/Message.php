<?php
class MessageModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_msg_type',
		'key' => 'mid'
	);

	function send($send_user,$create_user,$msgInfo){
		$mid = $this->getType($send_user,$create_user);
		$msgInfo['mid'] = $mid;
		$msgInfo['create_time'] = time();
		return $this->table("ptv_msg_content")->insert($msgInfo);
	}

	function getType($send_user,$create_user){
		$msg_type = $this->find("first",array(
			"where" => array('send_user' => $send_user,"create_user" => $create_user),
			"fields" => array("id")
		));
		return $msg_type?$msg_type->data("id"):$this->addType(compact("send_user","create_user"));
	}

	function addType($data){
		if($data['create_user']){
			$user = $this->table("members")->find("first",array(
				"where" => array('uid' => $uid),
				"fields" => array("username")
			));
			$data['create_username'] = $user->data("username");
		}else{
			$data['create_username'] = "system";
		}
		return $this->insert($data);
	}

	function get($uid){
		$msg = $this->find("all",array(
			"where" => array("send_user" => $uid),
			"fields" => array("id","create_user","create_username","msg_count")
		));
		return $msg?$msg->data():array();
	}

	function getContent($mid,$type='first'){
		$msg = $this->table("ptv_msg_content")->find($type,array(
			"where" => array("mid" => $mid),
			"fields" => array("content","create_time"),
			"order" => array("create_time" => "DESC")
		));
		return $msg?$msg->data():array();
	}

	function setRead($uid){
		$this->table("members")->update(array("new_msg" => 0),array("uid" => $uid));
	}
}