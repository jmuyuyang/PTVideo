<?php
class AdminUserModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'members',
		'key' => 'uid'
	);

	public function checkLogin($uid,$shell) {
		if($uid != ''){
			$userInfo = $this->find('first',array(
				'where' => array('uid' => $uid),
				'fields' => array('uid','username','password','is_admin')
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
			'where' => array('username' => $name,'is_admin' => 1)
		))->data();
		if($userInfo){
			if(md5($password."video") == $userInfo['password']){
				return array($userInfo['uid'],$this->getShell($name,$userInfo['password']));
			}
		}
		return false;
	}
	
	public function getShell($name,$pass){
		return md5($name.$pass.'videoAdmin');
	}
}