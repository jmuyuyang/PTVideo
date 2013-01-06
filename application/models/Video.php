<?php
class VideoModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_videoinput',
		'key' => 'fid'
	);

	public function add($data){
		$data['addtime'] = time();
		return $this->insert($data);
	}

	public function addVideoImg($data){
		return $this->table('ptv_video_images')->replace($data);
	}

	public function addCollect($data){
		$data['addtime'] = time();
		$this->table("ptv_video_collect")->insert($data);
	}

	public function sqlSearch($word,$class,$offset,$limit){
		$videoList = array();
		$where = array();
		$where['ftitle'] = array('like' => "%".$word."%");
		if($class) $where['videoselect'] = $class;
		$video = $this->find('all',array(
			'where' => $where,
			'fields' => array('fid')
		));
		if($video){
			$count = $video->rowCount();
			$video = $this->find("all",array(
				'where' => $where,
				'fields' => array('fid','ftitle','discription','addtime'),
				'offset' => $offset,
				'limit' => $limit
			));
			$videoList = $video->data();
			$videoList['count'] = $count;
		}
		return $videoList;
	}

	public function sphinxSearch($word,$class,$offset,$limit){
		$se = new Util_SphinxSearch();
		$se = $se->matchMode(SPH_MATCH_ALL)->limit($offset,$limit);
		if($class) $se->filter("videoselect",$class);
		$result = $se->search($word,"test2");
		$videoList = array();
		if($result[0]){
			$videoIds = array_keys($result[1]);
			$video = $this->find('all',array(
				'where' => array('fid' => $videoIds),
				'fields' => array('fid','ftitle','discription','addtime')
			));
			$videoList = $video->data();
			$videoList['count'] = $result[0];
		}
		return $videoList;
	}

	public function getVideosByClass($class,$limit,$offset = 0){
		$videos = $this->find('all',array(
			'alias' => array('ptv_video_images' => 'm','ptv_videoinput' => 'v'),
			'where' => array('v.videoselect' => $class),
			'fields' => array('v.fid','v.ftitle','v.videoselect','v.votes','v.score','v.addtime','v.videonum','m.uimgfile'),
			'leftJoin' => array('table' => 'ptv_video_images','fKey' => 'mid','pKey' => 'fid'),
			'order' => array('addtime' => 'ASC'),
			'limit' => $limit,
			'offset' => $offset	
		));
		$videoFiles = $videos?$videos->data():array();
		return $videoFiles;
	}

	public function getPopularVideos($class){
		if(!$popularVideos = Cache::get("populiarVideos")){
			$popularVideos = array();
			$video = $this->find('all',array(
				'alias' => array('ptv_video_images' => 'm','ptv_videoinput' => 'v'),
				'fields' => array('v.fid','v.ftitle','v.discription','v.videoselect','v.addtime','m.uimgfile'),
				'leftJoin' => array('table' => 'ptv_video_images','fKey' => 'mid','pKey' => 'fid'),
				'order' => array('addtime' => 'ASC'),
				'limit' => 10,
			));
			if($video){
				$popularVideos = $video->data();
				Cache::set("popularVideos",$popularVideos,3600);
			}
		}
		return $popularVideos;
	}

	public function getMemberVideos($class){
		if(!$memberVideos = Cache::get("memberVideo_".$class)){
			$memberVideos = array();
			$video = $this->find('all',array(
				'alias' => array('ptv_video_images' => 'm','ptv_videoinput' => 'v'),
				'where' => array('v.videoselect' => $class),
				'fields' => array('v.fid','v.ftitle','v.addtime','m.uimgfile'),
				'leftJoin' => array('table' => 'ptv_video_images','fKey' => 'mid','pKey' => 'fid'),
				'limit' => 4
			));
			if($video){
				$memberVideos = $video->data();
				Cache::set("memberVideo_".$class,$memberVideos,3600);
			}
		}
		return $memberVideos;
	}

	public function getVideoNum($class){
		$video = $this->find('all',array(
			'fields' => array('fid')
		));
		if($video) return $video->rowCount();
		return false;
	}

	public function getCollectNum(){
		$collect = $this->table('ptv_video_collect')->find('all',array(
			'fields' => array('cid')
		));
		if($collect) return $collect->rowCount();
		return false;
	}

	public function getVideoByVid($vid){
		if(!$videoInfo = Cache::get("video_".$vid)){
			$videoInfo = array();
			$video = $this->find('first',array(
				'alias' => array('ptv_video_images' => 'm','ptv_videoinput' => 'v'),
				'where' => array('v.fid' => $vid),
				'fields' => array('v.fid','v.fname','v.ftitle','v.videodir','v.videoselect','v.discription','v.votes','v.score','v.addtime','v.distanceinfo','v.videonum','m.uimgfile','m.imgfile'),
				'leftJoin' => array('table' => 'ptv_video_images','fKey' => 'mid','pKey' => 'fid'),
			));
			if($video){
				$videoInfo = $video->data();
				Cache::set("video_".$vid,$videoInfo,3600);
			}
		}
		return $videoInfo;
	}

	public function getCollects($limit,$offset = 0){
		$collect = $this->table('ptv_video_collect')->find('all',array(
			'fields' => array('cid','collectname','addtime','videoid'),
			'order' => array('addtime' => "DESC"),
			'limit' => $limit,
			'offset' => $offset
		));
		$collectInfo = array();
		if($collect){
			$collectInfo = $collect->data();
			foreach ($collectInfo as $item => $collect) {
				if(!$image = Cache::get("collect_".$collect['cid']."_image")){
					$videos = unserialize($collect['videoid']);
					$rand_id = $videos[array_rand($videos)];
					$image = $this->table('ptv_video_images')->find('first',array(
						'where' => array('mid' => $rand_id),
						'fields' => array('uimgfile')
					));
					$img = $image?$image->data():"";
					Cache::set("collect_".$collect['cid']."_image",$img,3600);
				}
				$collectInfo[$item]['image'] = $image;
			}
		}
		return $collectInfo;
	}

	public function getCollectVideoInfo($cid){
		$collect = $this->table("ptv_video_collect")->find('first',array(
			'where' => array('cid' => $cid),
			'fields' => array('cid','collectname','videoid')
		));
		if($collect){
			$collectInfo = $collect->data();
			$videoIds = unserialize($collectInfo['videoid']);
			$collectInfo['videos'] = array();
			foreach ($videoIds as $vid){
				$collectInfo['videos'][] = $this->getVideoByVid($vid);	
			}
			return $collectInfo;
		}
		return false;
	}
}