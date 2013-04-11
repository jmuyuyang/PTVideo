<?php
class VideoclassController extends Controller{
	
	function indexAction(){
		$class = $this->loadModel('Videoclass');
		$vClass = $class->find('all',array(
			'fields' => array('cid','name','parentid','ifprivate','ifchild')
		));
		$videoClass = $vClass?$vClass->data():array();
		$prid = array();
		$puid = array();
		$classname = array();
		foreach($videoClass as $class){
			$classname[$class['cid']][0] = $class['name'];
			$classname[$class['cid']][1] = $class['ifchild'];
			if($class['cid'] == 0) continue;
			if ($class['ifprivate'] == 1) $prid[$class['parentid']][] = $class['cid'];
			if ($class['ifprivate'] == 0) $puid[$class['parentid']][] = $class['cid'];
		}
		if($this->request->query['update']){
			$config_file = $this->app_config->directory."/config/class_config.php";
			$this->_writeConfig($config_file,$classname,'classInfo',1,0);
			$this->_writeConfig($config_file,$puid,'puclass',0,0);
			$this->_writeConfig($config_file,$prid,'prclass',0,1);
		}
		$params = compact('prid','puid','classname');
		$this->getView()->assign($params);
	}

	function updateAction(){
		$action = $this->request->params['action'];
		$method = "_".$action;
		$this->$method();
	}

	function _del(){
		$id = $this->request->query['id'];
		$class = $this->loadModel('Videoclass');
		$del = $class->remove($id);
		if($del){
			$this->sendMsg('/admin/videoclass/?update=1','删除成功');
		}
		$this->sendMsg('/admin/videoclass','删除失败');
	}

	private function _add(){
		$name = $this->request->data['name'];
		$ifprivate = $this->request->data['ifprivate'];
		$parentid = $this->request->data['parentid'];
		$class = $this->loadModel('Videoclass');
		if($name){
			$class->add(array('name' => $name,'ifprivate' => $ifprivate,'parentid' => $parentid));
			$this->sendMsg('/admin/videoclass/?update=1','成功添加分类');
		}
		$this->sendMsg('/admin/videoclass/','添加类别失败');
	}

	private function _update(){
		$id = $this->request->data['id'];
		$name = $this->request->data['name'];
		$class = $this->loadModel('Videoclass');
		if($id && $name){
			$update = $class->update(array('name' => $name),array('cid' => $id));
			if($update) $this->sendMsg('/admin/videoclass/?update=1','更新成功');
		}
		$this->sendMsg('/admin/videoclass/','更新失败');
	}

	private function _writeConfig($file,$input,$flagName,$first,$last){
		static $data;
		if($first) $data = "<?php\n\r";
		$data.= "\n\r\$".$flagName."=".Util_String::varToString($input).";";
		if($last){
			$data.="\n\r?>";
			$fp = fopen($file,'w');
			fwrite($fp,$data);
			fclose($fp);
		}
	}

}