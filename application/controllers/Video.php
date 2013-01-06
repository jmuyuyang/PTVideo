<?php
class VideoController extends Controller{

	function init(){
		parent::init();
		$this->setLayout();
	}

	function playAction(){
		$vid = $this->request->params['vid'];
		$uid = $this->_userInfo['uid'];
		$index = $this->request->query['index']?:1;
		$video = $this->loadModel("Video");
		$videoInfo = $video->getVideoByVid($vid);
		$videoInfo['play_index'] = $index;
		$playConfig = $this->_initPlay($videoInfo,$index);
		$memberVideos = $video->getMemberVideos($videoInfo['videoselect']);
		$comments = $this->loadModel("Comment")->get($vid);
		$voteScore = $this->loadModel("Vote")->get($uid,$vid);
		$class = $this->_getClass($videoInfo['videoselect']);
		$this->getView()->assign("current_class",$class);
		$this->getView()->assign(array('videoInfo' => $videoInfo,'memberVideos' => $memberVideos,'comments' => $comments,'voteScore' => $voteScore,'playConfig' => $playConfig));
	}

	function searchAction(){
		$this->getView()->assign("current_class",0);
	}

	function searchVideoAction(){
		$word = urldecode($this->request->params['keyw']);
		$class = $this->request->query['cl']?:0;
		$page = $this->request->query['page']?:1;
		$videoList = $this->_search($word,$class,($page-1)*10,10);
		$pages = ceil($videoList['count']/10);
		$uri = $this->_getRequestUri();
		unset($videoList['count']);
		$this->getView()->assign("current_class",$class);
		$this->getView()->assign(compact("uri","page","pages","word","videoList"));
	}

	function voteAction(){
		$vote = $this->loadModel("Vote");
		$vid = $this->request->query['vid'];
		$uid = $this->_userInfo['uid'];
		$score = (int)$this->request->data['score'];
		if(!$vote->get($uid,$vid)){
			$add = $vote->add($uid,$vid,$score);
			if($add) {
				$this->response->setBody(json_encode(array('operation' => 'successful')));
				return;
			}
		}
		$this->response->setBody(json_encode(array('operation' => 'failed')));
	}

	function commentAction(){
		$uid = $this->_userInfo['uid'];
		$author = $this->_userInfo['username'];
		$content = $this->request->data['content'];
		$fid = $this->request->query['vid'];
		$reply = $this->request->query['reply']?:0;
		$comment = $this->loadModel("Comment");
		$addComment = $comment->add(compact('fid','uid','author','content','reply'));
		$time = strftime("%Y-%m-%d",time());
		if($addComment) $this->response->setBody(json_encode(compact("author","content","time")));
		else $this->response->setBody(json_encode(array('operation' => 'failed')));
	}

	function getCommentsAction(){
		$cid = $this->request->query['cid'];
		$vid = $this->request->params['vid'];
		$comment = $this->loadModel("Comment");
		$comments = $comment->get($vid,10);
		$this->response->setBody(json_encode($comments));
	}

	private function _initPlay($videoInfo,$index = 1){
		if($videoInfo['distanceinfo']){
			if($videoInfo['videonum']){
				$file = Util_Video::getVfInfo($this->app_config['video_file_dir'].$videoInfo['fname'],$index);
			}else{
				$file = $videoInfo['fname'];
			}
			if(preg_match("#http:\/\/player.youku.com\/player.php\/sid\/(\w+)\/v\.swf#",$file,$match)){
				$file = $match[1];
			}
		}else{
			$file = $videoInfo['videodir']."/".$videoInfo['fname'];
			if($videoInfo['videonum']){
				$file.="/".$index;
			}else{
				$file.="/".$videoInfo['fname'];
			}
		}
		return array('file' => $file,'rtmp_server' => $this->app_config['rtmp_server'],'http_server' => $this->app_config['http_server']);
	}

	private function _search($word,$class,$offset,$limit){
		$config = Yaf_Registry::get("config")->search->toArray();
		if($config['type'] == "sphinx") Util_SphinxSearch::setServer($config['host'],$config['port']);
		$method = $config['type']."Search";
		if($class){
			$class = isset($this->classConfig['classes'][$class])?$this->classConfig['classes'][$class]:array($class);
		}
		$video = $this->loadModel("Video");
		return $video->{$method}($word,$class,$offset,$limit);
	}

	private function _getClass($class){
		$videoClass = $this->loadModel("Videoclass");
		return $videoClass->getParentClass($class)?:$class;
	}

	private function _getRequestUri(){
		$uri = $this->request->getServer("REQUEST_URI");
		if($pos = strpos($uri, "page")) $uri=substr($uri,0,$pos-1); 
		if(!strpos($uri,"?")) $uri.="?";
		else $uri.="&";
		return $uri;
	}
}

?>