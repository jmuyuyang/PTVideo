<?php
class IndexController extends Controller{
	
	function init(){
		parent::init();
		$this->setLayout();
	}

	function IndexAction(){
		$class = $this->request->query['id']?:0;
		$video = $this->loadModel('Video');	
		$popularClass = array_merge($this->classConfig['classes'][3],$this->classConfig['classes'][3]);
		$popularVideos = $video->getPopularVideos($popularClass);
		$collectInfo = $this->_getVideoCollects();
		$classVideos = $this->_getClassVideos($class);
		$this->getView()->assign('current_class',$class);
		$this->getView()->assign(compact('classVideos','popularVideos','collectInfo'));
	}

	function collectListAction(){
		$cid = $this->request->params['cid'];
		$video = $this->loadModel('Video');
		$page = $this->request->query['page']?:1;
		$offset = $this->pagenator($video->getCollectNum(),20,$page);
		$collects = $video->getCollects(20,$offset);
		$this->getView()->assign('collects',$collects);
	}
	
	function classVideoListAction(){
		$cid = $this->request->params['cid'];
		$page = $this->request->query['page']?:1;
		$video = $this->loadModel("Video");
		$offset = $this->pagenator($video->getVideoNum($cid),20,$page);
		$videos = $video->getVideosByClass($cid,20,$offset);
		$class = $this->loadModel("Videoclass")->getParentClass($cid)?:$cid;
		$this->getView()->assign('current_class',$class);
		$this->getView()->assign('videos',$videos);
	}

	function collectVideoListAction(){
		$cid = $this->request->params['cid'];
		$video = $this->loadModel('Video');
		$collectInfo = $video->getCollectVideoInfo($cid);
		$this->getView()->assign('current_class',0);
		$this->getView()->assign('collectInfo',$collectInfo);
	}

	private function _getVideoCollects(){
		$video = $this->loadModel('Video');
		$collectInfo = $video->getCollects(4);
		return $collectInfo;
	}

	private function _getClassVideos($class){
		$classVideos = array();
		$video = $this->loadModel('Video');
		foreach($this->classConfig['classes'][$class] as $class){
			$where = $class;
			if($this->classConfig['classInfo'][$class][1] == 0){
				$where = $this->classConfig['classes'][$class];
 			}
 			if(!$classVideos[$class] = Cache::get("class_videos_".$class)){
 				$classVideos[$class] = $video->getVideosByClass($where,3);
 				Cache::set("class_videos_".$class,$classVideos[$class],3600);
 			}
		}
		return $classVideos;
	}
}