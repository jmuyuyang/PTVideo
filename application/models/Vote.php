<?php
class VoteModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_video_votes',
		'key' => 'fid'
	);

	function add($uid,$fid,$score){
		$update = $this->table("ptv_videoinput")->update(array("score" => array("inc" => $score),"votes" => array("inc" => 1)),array("fid" => $fid));
		$add = $this->insert(array("vid" => $fid,"uid" => $uid,"score" => $score));
		if($update && $add){
			return true;
		}
		return false;
	}

	function get($uid,$fid){
		$vote = $this->find('first',array(
			'where' => array("uid" => $uid,"vid" => $fid),
			'fields' => array('score')
		));
		return $vote?$vote->data("score"):false;
	}
}