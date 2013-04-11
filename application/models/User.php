<?php
class UserModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'members',
		'key' => 'uid'
	);

	public $validator = array(
		'email' => array('email','message' => '请输入正确的email')
	);

	public function profile($uid){
		$profile = $this->find("first",array(
			"where" => array("uid" => $uid),
			"fields" => array("uid","username","password","is_admin","new_msg","email")
		));
		return $profile?$profile->data():array();
	} 

	public function add($userInfo){
		$userInfo['password'] = md5($userInfo['password']."video");
		$userInfo['addtime'] = time();
		$signup = $this->create($userInfo);
		if($signup) return $signup->save();
		return false;
	}

	public function modifyPass($uid,$passw,$passnw){
		$user = $this->find('first',array(
			'where' => array('uid' => $uid),
			'fields' => array('uid','username','password')
		));
		if($user && $user->password == md5($passw."video")){
			$user->password = md5($passnw);
			return $user->save('update');
		}
		return false;
	}

	public function modifyProfile($uid,$data){
		return $this->update($data,array("uid" => $uid));
	}

	public function checkLogin($uid,$shell) {
		if($uid != ''){
			$userInfo = $this->profile($uid);
			if($userInfo){
				if($shell == $this->_getShell($userInfo['username'],$userInfo['password'])){
					unset($userInfo['password']);
					return $userInfo;
				}
 			}
 		}
		return false;
	} 

	public function login($name,$password,$is_admin = 0){
		$where = array('username' => $name);
		if($is_admin) $where['is_admin'] = 1;
		$userInfo = $this->find('first',array(
			'where' => $where
		));
		if($userInfo){
			$userInfo = $userInfo->data();
			if(md5($password."video") == $userInfo['password']){
				return array($userInfo['uid'],$this->_getShell($name,$userInfo['password']));
			}
		}
		return false;
	}

	public function userCheck($where){
		$user = $this->find("first",array(
			'where' => $where,
			'fields' => array('uid')
		));
		return $user?true:false;
	}

	public function getList($search,$is_admin,$limit,$offset){
		$where = array();
		if($search){
			$where["username"] = array("like" => "%".urldecode($search)."%");
		}
		if(isset($is_admin)){
			$where['is_admin'] = $is_admin;
		}
		$user = $this->find('all',array(
			'where' => $where,
			'fields' => array('uid','username','email','is_admin'),
			'limit' => $limit,
			'offset' => $offset
		));
		$userList = $user?$user->data():array();
		return $userList;
	}

	public function setPower($uid,$power){
		return $this->update(array('is_admin' => $power),array('uid' => $uid));
	}

	public function updateNewMsg($uid){
		$this->update(array("new_msg" => array("inc" => 1)),array("uid" => $uid));
	}

	public function resetNewMsg($uid){
		$this->update(array("new_msg" => 0),array("uid" => $uid));
	}

	private function _getShell($name,$pass){
		return md5($name.$pass."video");
	}
}