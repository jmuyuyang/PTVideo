<?php
class VideoController extends Controller{
//video create update delete;video collect create
	
	function indexAction(){
		$classList = $this->_classList();
		$file = $this->request->query['file'];	
		$fid = $this->request->query['fid'];
		if($fid){
			$info = $this->loadModel("Video")->find("first",array(
				'where' => array('fid' => $fid),
				'fields' => array('ftitle','videonum')
			));
			if(!$info) $this->sendMsg("/admin/newvideo","无法为该影视添加视频");
			$this->getView()->assign(array("dname" => $info->data('ftitle'),"videonum" => $info->data('videonum')));
		}
		$this->getView()->assign(array('classList' => $classList,'file' => $file,'fid' => $fid));
	}

	function videoAction(){
		$action = $this->request->params['action'];
		if($action){
			$method = "_".$action;
			if($this->{$method}()){
				$this->sendMsg("/admin/videoclass","操作成功");
			}
		}
		$this->sendMsg("/admin/videoclass","操作失败");
	}

	function updateAction(){
		$fid = $this->request->params['fid'];
		$classList = $this->_classList();
		$video = $this->loadModel('Video');
		$videoInfo = $video->find('first',array(
			'where' => array('fid' => $fid),
			'fields' => array('fid', 'fname', 'ftitle', 'discription', 'videoselect', 'videokbs', 'videonum', 'distanceinfo')
		));
		$videoFileInfo = $videoInfo?$videoInfo->data():array();
		$this->getView()->assign('videoFileInfo',$videoFileInfo);
		$this->getView()->assign('classList',$classList);
	}

	function addCollectAction(){
		$videoArray = array();
		$videoId = $this->request->data['videoid'];
		$collectName = $this->request->data['collectname'];
		$collectContent = $this->request->data['collectcontent'];
		$video = $this->loadModel('Video');
		if(is_array($videoId)){
			$videoCollect = serialize($videoId);
			$add = $video->addCollect(array('collectname'=> $collectName,'contents'=> $collectContent,'videoid' => $videoCollect));
			if($add) $this->sendMsg('/admin/videoclass','合集信息添加成功');
		}
		$this->sendMsg('/admin/videoclass','添加失败');
	}

	function grabDoubanAction(){
		if($imdb = $this->request->query['imdb']){
			$info = new Util_DoubanInfo(1,trim($imdb));
			$returnInfo = $info -> getInfo();
			if ($returnInfo === false) {
				$this->response->setBody(json_encode(array('error'=>1)));
			}else{ 
				$title = $info -> getTitle();
				$content = $info -> getFormatInfo();
				$this->response->setBody(json_encode(array('title'=>$title,'content'=>$content,'error'=>0)));//文件
			}
		}	
	}

	private function _add(){
		print_r($this->request->data);
		$file = $this->request->data['file'];
		if($this->request->data['ifCreate']){
			$title = $this->request->data['title'];
			$content = $this->request->data['content'];
			$videoSelect = $this->request->data['videoselect'];
			$videonum = $this->request->data['videonum'];
			if(!$title || !$content || !$videoSelect){
				$this->sendMsg("/admin/newvideo", "请输入完整信息");
			}
			$fClass = $this->loadModel('Videoclass')->getParentClass($videoSelect);
			$moveDir = $this->app_config->video_dir;
			list($moveDir,$filename) = $this->_uploadVideo($file,$moveDir,$videonum);
			$video = $this->loadModel('Video');
			$data = array(
				'fname' => $filename,
				'ftitle' => $title,
				'videoselect' => $videoSelect,
				'videodir' => $moveDir,
				'fclass' => $fClass,
				'discription' => $content,
				'videonum' => $videonum,
				'distanceinfo' => $this->request->data['distance'],
			);
			$vid = $video->add($data);
			if(Cache::get("video_".$vid)){
				Cache::delete("video_".$vid);
			}
			if($vid && $_FILES['imgfile']['name'] != ''){
				return $this->_uploadDiscribeImg($vid,$_FILES['imgfile']);
			}
			return false;
		}else{
			$fid = $this->request->data['fid'];
			$tmpnum = $this->request->data['tmpnum']?:0;
			$this->_addMoreVideo($fid,$file,$tmpnum);
			return true;
		}
	}

	private function _update(){
		$fid = $this->request->data['fid'];
		$title = $this->request->data['title'];
		$content = $this->request->data['content'];
		$videonum = $this->request->data['videonum'];
		$videoSelect = $this->request->data['videoselect'];
		if(!$title || !$content || $videoSelect == 0){
			$this->sendMsg('/admin/newvideo','请输入完整信息');
		}
		if(!$this->request->data['distance']){
			//$this->_movieScreen($this->request->data['mType'], $file);
		}
		if($_FILES['imgfile']['name'] != ''){
			$this->_uploadDiscribeImg($fid,$_FILES['imgfile']);
		}
		$video = $this->loadModel('Video');
		return $update = $video->update(array(
			'fname' => $file,
			'ftitle' => $title,
			'discription' => $content,
			'videoselect' => $videoSelect,
			'videonum' => $videonum,
			'distanceinfo' => $this->request->data['distance'],
		),array('fid' => $fid));
		if(Cache::get("video_".$fid)){
			Cache::delete("video_".$fid);
		}
	}

	private function _delete(){
		$fid = $this->request->query['fid'];
		$video = $this->loadModel('Video');
		$del = $video->delete(array('fid' => $fid));
		if($del){
			if(Cache::get("video_".$vid)){
				Cache::delete("video_".$vid);
			}
			return true;
		}
		return false;
	}

	private function _addMoreVideo($fid,$file,$tmpnum){
		$video = $this->loadModel("Video");
		$info = $video->find("first",array(
			"where" => array("fid" => $fid),
			"fields" => array("fname","videonum","videodir")
		))->data();
		$moveFile = $this->app_config->video_dir.$info['videodir']."/".$info['fname']."/";
		if(!$tmpnum){
			$moveFile .= $info['fname'];
		}else{
			$video->update(array('videonum' => array('inc' => $tmpnum)),array('fid' => $fid));
		}
		$this->_uploadVideo($file,$moveFile,$info['videonum']);
	}

	private function _uploadVideo($file,$moveFile,$videonum = 0){
		if(!$this->request->data['distance']){
			$ifCreate = $this->request->data['ifCreate'];
			$orginFile = $this->app_config->tmp_video_dir.stripslashes($file);
			if(!$videonum){
				$videotype = $this->_checkType($file);
				if(!$videotype) $this->sendMsg('/admin/newvideo','文件格式不正确');
				if(!$ifCreate) $moveFile.=".".$videotype;
			}
			//$videokbs = $this->_movieScreen($this->request->data['mType'], $filename);
			return Util_Video::moveFile($orginFile,$moveFile,$videotype,$ifCreate);
		}else{
			$filename = $this->_uploadVideoFile($file);
			return array("",$filename);
		}
	}

	private function _uploadVideoFile($file){
		if($_FILES['file']){
			Util_Upload::config(array(
				'allowTypes' => array('txt'),
				'savePath' => $this->app_config->video_file_dir
			));
			$uploadInfo = $this->_upload($_FILES['file'])->getUploadFileInfo();
			$filename = $uploadInfo['uploadFile'];
		}else{
			$filename = $file;
		}
		return $filename;
	}

	private function _checkType($file){
		$info = pathinfo($file);
		if(in_array($info['extension'], array('mp4','webm'))){
			return $info['extension'];
		}
		return false;
	}

	private function _movieScreen($type,$file){
		if ($type == 1) {
			$frame = $this->request->data['mframe']?:1;
			$movieinfo = Util_Video::movie_mplayer($filename, $this->app_config->video_dir,$frame);
		}else{
			$_fs = $_FILES['screenfile'];
			Util_Upload::config(array(
				'allowTypes' => array('jpg'),
				'savePath' => $this->app_config->video_movie_img
			));
			$this->_uploadFile($_fs);
		}
	}

	private function _uploadDiscribeImg($vid,$file){
		Util_Upload::config(array(
			'allowTypes' => array('jpeg', 'jpg', 'png', 'gif'),
			'maxSize' => 9999999,
			'savePath' => $this->app_config->upload_img,
			'thumbDir' => $this->app_config->upload_thumb_img
		));
		$uploadFile = $this->_upload($file);
		if(!$uploadFile) $this->sendMsg('/admin/newvideo','文件上传失败:'.$this->upload->errors());
		$uploadFile->adapter('thumb',array(120,165))->saveThumb();
		$uploadInfo = $uploadFile->getUploadFileInfo();
		$video = $this->loadModel("Video");
		return $video->addVideoImg(array('mid' => $vid,'imgfile' => $uploadInfo['uploadFile'],'uimgfile' => $uploadInfo['thumbName']));
	}

	private function _upload($file){
		$this->upload = $this->upload?:new Util_Upload();
		$upload = $this->upload->upload($file);
		if($upload) {
			$upload->save();
			return $upload;
		}
		return false;
	}

	private function _classList(){
		$this->loadClassConfig();
		$classList = array();
		$classes = $this->classConfig['classes'];
		foreach($classes[0] as $class){
			$classList[$class]['name'] = $this->classConfig['classInfo'][$class][0];
			$classList[$class]['disabled'] = !$this->classConfig['classInfo'][$class][1];
			if($classes[$class]){
				foreach($classes[$class] as $subclass){
					$classList[$subclass]['name'] = "--".$this->classConfig['classInfo'][$subclass][0];
					$classList[$subclass]['disabled'] = 0;
				}
			}
		}
		return $classList;
	}
}
