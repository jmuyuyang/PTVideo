<?php
define("APPLICATION_PATH",  dirname(__FILE__));
define("PUBLIC_PATH",  dirname(__FILE__).'public/');

$app  = new Yaf_Application(APPLICATION_PATH . "/conf/application.ini");
$app->bootstrap() //call bootstrap methods defined in Bootstrap.php
    ->run();
?>