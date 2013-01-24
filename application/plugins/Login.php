<?php
class LoginPlugin extends Yaf_Plugin_Abstract{

	public $redirect = true;

	function routerShutdown(Yaf_Request_Abstract $request , Yaf_Response_Abstract $response){
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		if($controller == "Index" && $action == "index") $this->redirect = false;
	}

	function preDispatch(Yaf_Request_Abstract $request , Yaf_Response_Abstract $response){
		$module = strtolower($request->getModuleName());
		$controller = $request->getControllerName();
		$method = "_".$module."LoginCheck";
		if($controller != "User"){
			if(!$check = $this->{$method}($request)) {
				$this->_setRedirect($request,$response);
			}
		}else{
			if($check = $this->{$method}($request)){
				$response->setRedirect("/");
			}
		}
	}

	private function _setRedirect($request,$response){
		if($request->isXmlHttpRequest()) {
			$response->setBody(json(array('error' => 'true')));
			exit();
		}
		if($request->getModuleName() == "Index") {
			if($this->redirect) $response->setRedirect('/login?url='.$request->getRequestUri());
		}
		else echo "<script type='text/javascript'>parent.location.href='/admin/login'</script>";
	}

	private function _indexLoginCheck($request){
		$user = new HomeUserModel();
		$cookie = Util_Cookie::getInstance();
		$uid = $cookie->huid;
		$shell = $cookie->hshell;
		if($uid && $userInfo = $user->checkLogin($uid,$shell)){
			$request->setCurrentUser($userInfo);
			return true;
		}
		return false;
	}

	private function _adminLoginCheck($request){
		$user = new AdminUserModel();
		$session = Yaf_Session::getInstance();
		$uid = $session->auid;
		$shell = $session->ashell;
		if($uid && $userInfo = $user->checkLogin($uid,$shell)){
			$request->setCurrentUser($userInfo);
			return true;
		}
		return false;
	}
}