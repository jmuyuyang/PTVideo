<?php
class VideolistController extends Controller{
//video or new tmp video list video search
	function indexAction(){
		$action = $this->request->params['action'];
		if($action == 'list'){
			$videoFiles = $this->_videoList();
		}
		if($action == 'search'){
			$videoFiles = $this->_videoSearch();
		}
		$this->getView()->assign('videoFiles',$videoFiles);
	}

	function newVideoAction(){
		$uid = $this->request->query['uid']?:0;
		$dname = $uid?$this->_getUploadDir($uid)."所在目录":"影视根目录";
		$fileInfo = Util_Video::fileInfo($this->app_config->tmp_video_dir);
		$this->getView()->assign('fileInfo',$fileInfo);
		$this->getView()->assign(compact("uid","dname"));
	}

	private function _getUploadDir($fid){
		$video = $this->loadModel("Video");
		$info = $video->find("first",array(
			'where' => array("fid" => $fid),
			'fields' => array('fid','ftitle')
		));
		return $info?$info->data('ftitle'):"";
	}

	private function _videoList(){
		$video = $this->loadModel('Video');
		$select = $this->request->query['select']?:0;
		$cname = $this->request->query['cname'];
		$vFile = $video->find('all',array(
			'where' => array('videoselect' => $select),
			'fields' => array('fid','fname','ftitle','videoselect')
		));
		$videoFile = $vFile?$vFile->data():array();
		$this->getView()->assign('cname',$cname);
		return $videoFile;
	}

	private function _videoSearch(){
		if($search = $this->request->data['search']){
			$video = $this->loadModel('Video');
			$videoList = $video->find('all',array(
				'where' => array('like' => array('ftitle' => "%".$search."%")),
				'fields' => array('fid','fname','ftitle','videoselect')
			));
			$videoFiles = $videoList?$videoList->data():array();
		}
		$this->getView()->assign('cname','搜索'.$search);
		return $videoFiles;
	}
}