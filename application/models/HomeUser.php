<?php
class HomeUserModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'members',
		'key' => 'uid'
	);

	public $validator = array(
		'email' => array('email','message' => '请输入正确的email')
	);

	public function checkLogin($uid,$shell) {
		if($uid != ''){
			$userInfo = $this->find('first',array(
				'where' => array('uid' => $uid),
				'fields' => array('uid','username','password','is_admin','new_msg')
			))->data();
			if($userInfo){
				if($shell == $this->getShell($userInfo['username'],$userInfo['password'])){
					unset($userInfo['password']);
					return $userInfo;
				}
 			}
 		}
		return false;
	} 

	public function login($name,$password){
		$name = str_replace(" ", "", $name);
		$userInfo = $this->find('first',array(
			'where' => array('username' => $name)
		));
		if($userInfo){
			$userInfo = $userInfo->data();
			if(md5($password."video") == $userInfo['password']){
				return array($userInfo['uid'],$this->getShell($name,$userInfo['password']));
			}
		}
		return false;
	}

	public function signup($userInfo){
		$userInfo['password'] = md5($userInfo['password']."video");
		$userInfo['addtime'] = time();
		$signup = $this->create($userInfo);
		if($signup) return $signup->save();
		return false;
	}

	public function updatePass($name,$passnw,$passw){
		$user = $this->find('first',array(
			'where' => array('username' => $name,'password' => md5($passw)),
			'fields' => array('uid','username','password')
		));
		if($user){
			$user->password = md5($passnw);
			return $user->save('update');
		}
		return false;
	} 

	public function userCheck($val){
		$user = $this->find("first",array(
			'where' => array('username' => $val),
			'fields' => array('uid')
		));
		return $user?true:false;
	}

	public function emailCheck($val){
		$user = $this->find("first",array(
			'where' => array('email' => $val),
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

	public function getShell($name,$pass){
		return md5($name.$pass.'videoHome');
	}
}