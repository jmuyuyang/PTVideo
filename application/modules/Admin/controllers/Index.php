<?php
class IndexController extends Controller{
	
	function IndexAction(){
	}

	function naviAction(){
		$webroot = $this->app_config->webroot;
		$uname = $this->_userInfo['username'];
		$this->getView()->assign('uname',$uname);
	}

	function logOutAction(){
		session_destroy();
		echo "<script type='text/javascript'>parent.location.href='/admin/login'</script>";
		Yaf_Dispatcher::getInstance()->disableView();
	}
}