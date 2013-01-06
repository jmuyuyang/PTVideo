<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

	function _initConfig(){
		$config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config',$config);
	}

	public function _initRequest(Yaf_Dispatcher $dispatcher){
        $dispatcher->setRequest(new Request());
    }

	public function _initRoute(){
		$route_config = new Yaf_Config_Ini(APPLICATION_PATH . "/conf/route.ini");
		$route = Yaf_Dispatcher::getInstance()->getRouter();
		$route->addConfig($route_config->routes);
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher){
        $login = new LoginPlugin();
        Yaf_Registry::set('login', $layout);
        $dispatcher->registerPlugin($login);
	}

	public function _initLayout(Yaf_Dispatcher $dispatcher){
		$layout = new LayoutPlugin();
		Yaf_Registry::set("layout",$layout);
		$dispatcher->registerPlugin($layout);
	}

	public function _initCache(){
		$cacheConfig = Yaf_Registry::get("config")->cache->toArray();
		Cache::config($cacheConfig);
	}

	public function _initCookie(){
		Util_Cookie::config(array(
			'expires' => 172800,
			'encode' => true
		));
	}

}