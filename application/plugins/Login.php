<?php
class LoginPlugin extends Yaf_Plugin_Abstract{

	function preDispatch(Yaf_Request_Abstract $request , Yaf_Response_Abstract $response){
		$check = $this->_loginCheck($request);
		$this->_setRedirect($request,$check);
	}

	private function _setRedirect($request,$check){
		$controller = $request->getControllerName();
		if($request->getRequestUri() == '/') return;
		if($controller == "Login"){
			if($check) $request->setParam("redirect","/");
			return;
		}
		if(!$check) $request->setParam("redirect",'/login?url='.$request->getRequestUri());
	}

	private function _loginCheck($request){
		$module = strtolower($request->getModuleName());
		list($uid,$shell) = $this->{"_".$module."Check"}();
		if($uid){
			$user = new UserModel();
			$userInfo = $user->checkLogin($uid,$shell);
			if($userInfo){
				$request->userInfo = $userInfo;
				return true;
			}
		}
		return false;
	}

	private function _indexCheck(){
		$cookie = Util_Cookie::getInstance();
		$uid = $cookie->huid;
		$shell = $cookie->hshell;
		return array($uid,$shell);
	}

	private function _adminCheck(){
		$session = Yaf_Session::getInstance();
		$uid = $session->auid;
		$shell = $session->ashell;
		return array($uid,$shell);
	}
}