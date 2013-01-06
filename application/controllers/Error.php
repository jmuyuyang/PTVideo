<?php 
class ErrorController extends Yaf_Controller_Abstract {
    
    public function errorAction($exception) {
        
        switch ($exception->getCode()) {
            case YAF_ERR_AUTOLOAD_FAILED:
            case YAF_ERR_NOTFOUND_MODULE:
            case YAF_ERR_NOTFOUND_CONTROLLER:
            case YAF_ERR_NOTFOUND_ACTION:
                header('HTTP/1.0 404 Not Found');
                break;
            default:
                header("HTTP/1.0 500 Internal Server Error");
                break;
        }

        // fallback views path to global when error occured in modules.
        $config = Yaf_Application::app()->getConfig();
        $this->getView()->setScriptPath($config->application->directory 
            . "/views");
         
        $this->getView()->e = $exception;
        $this->getView()->e_class = get_class($exception);
        $this->getView()->e_string_trace = $exception->getTraceAsString();

        $params = $this->getRequest()->getParams();
        unset($params['exception']);
        $this->getView()->params = array_merge(
            array(),
            $params,
            $this->getRequest()->getPost(),
            $this->getRequest()->getQuery()
        );
    }
}
?>