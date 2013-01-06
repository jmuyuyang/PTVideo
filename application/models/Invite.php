<?php
class inviteModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_invites',
		'key' => 'uid'
	);

	private $_expire_day = 3;

	function add($data){
		$expire_time = time()+60*60*24*$this->_expire_day;
		$data['expire'] = $expire_time;	
		return $this->insert($data);
	}

	function check($hash){
		$invite = $this->find("first",array(
			"where" => array("hash" => $hash),
			"fields" => array("id","invite_user","hash","expire")
		));
		if($invite){
			$info = $invite->data();
			if($info['used']){
				return array("errors" => "邀请码已被使用");
			}
			if($info['expire'] < time()){
				return array("errors" => "邀请码已过期");
			}
			return $info;
		}
		return array("errors" => "邀请码不存在");
	}

	function setUsed($id,$uid){
		return $this->update(array("used" => $uid),array("id" => $id));
	}

	function getList($uid){
		$invite = $this->find("all",array(
			"where" => array("invite_user" => $uid),
			"fields" => array("hash","expire","used")
		));
		return $invite?$invite->data():array();
	}
}

?>