<?php
class IndexController extends Controller{
	
	function IndexAction(){
	}

	function naviAction(){
		$webroot = $this->app_config->webroot;
		$uname = $this->_userInfo['username'];
		$this->getView()->assign('uname',$uname);
	}
}